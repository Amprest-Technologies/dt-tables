<?php

namespace Amprest\DtTables\Support;

use Amprest\DtTables\Tables\ServerSideTable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

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
        //  Only server-side table classes may be served
        if (! is_subclass_of($this->class, ServerSideTable::class)) {
            throw new InvalidArgumentException(
                "[{$this->class}] must extend ".ServerSideTable::class
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

        //  Reject a payload that is not a server-side table class
        abort_unless(is_array($payload) && is_string($payload['class'] ?? null), 403, 'Invalid data table source');

        //  Reject a class that is not a server-side table
        abort_unless(is_subclass_of($payload['class'], ServerSideTable::class), 403, 'Invalid data table source');

        //  Return the source
        return new static($payload['class'], $payload['params'] ?? []);
    }

    /**
     * Instantiate the table with its sealed parameters.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function instance(): ServerSideTable
    {
        return new $this->class(...$this->params);
    }
}
