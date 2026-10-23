<?php

namespace Nyumbani\DtTables\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use Nyumbani\DtTables\Tables\ServerSideSource;

class TableSource
{
    /**
     * Create a new table source.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(
        public string $class,
        public array $params = [],
    ) {
        if (! is_subclass_of($this->class, ServerSideSource::class)) {
            throw new InvalidArgumentException(
                "[{$this->class}] must extend ".ServerSideSource::class
            );
        }
    }

    /**
     * Seal a table class and its parameters into a token the browser cannot alter.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function seal(string $class, array $params = []): string
    {
        //  Validate the class before it is sealed
        new static($class, $params);

        //  Encrypt the class and parameters
        return Crypt::encryptString(json_encode([
            'class' => $class,
            'params' => $params,
        ]));
    }

    /**
     * Open a token, rejecting anything the component did not issue.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function open(string $token): static
    {
        //  Decrypt the token
        try {
            $payload = json_decode(Crypt::decryptString($token), true);

            //  Reject an invalid token
        } catch (DecryptException) {
            abort(403, 'Invalid data table token');
        }

        //  Reject a payload that is not a server-side source class
        abort_unless(is_array($payload) && is_string($payload['class'] ?? null), 403, 'Invalid data table source');

        //  Reject a class that is not a server-side source
        abort_unless(is_subclass_of($payload['class'], ServerSideSource::class), 403, 'Invalid data table source');

        //  Return the source
        return new static($payload['class'], $payload['params'] ?? []);
    }

    /**
     * Instantiate the table with its sealed parameters.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function instance(): ServerSideSource
    {
        return new $this->class(...$this->params);
    }
}
