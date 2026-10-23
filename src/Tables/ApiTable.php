<?php

namespace Nyumbani\DtTables\Tables;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Nyumbani\DtTables\Exceptions\RemoteSourceException;
use Nyumbani\DtTables\Support\ApiQuery;
use Nyumbani\DtTables\Support\Page;
use Nyumbani\DtTables\Support\PageRequest;
use Nyumbani\DtTables\Support\ServerSideRunner;

abstract class ApiTable extends ServerSideSource
{
    /**
     * Return the configured HTTP client, with its base url, credentials and timeouts.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function client(): PendingRequest;

    /**
     * Return the endpoint, relative to the client's base url, that serves the rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    abstract public function endpoint(): string;

    /**
     * Return the HTTP verb used to read a page, post is useful when a request carries long lists.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function method(): string
    {
        return 'get';
    }

    /**
     * Translate the requested page into the API's request parameters.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function params(PageRequest $page): array
    {
        //  Describe the page, search and the first ordering
        return array_filter([
            'page' => $page->page(),
            'per_page' => $page->length,
            'search' => $page->search,
            'search_fields' => $page->searchFields,
            'filters' => $page->filterTerms(),
            'sort' => $page->orders[0]['field'] ?? null,
            'dir' => $page->orders[0]['direction'] ?? null,
        ], fn ($value) => filled($value));
    }

    /**
     * Read a page out of the API's response, which defaults to a Laravel resource collection.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function parse(Response $response): Page
    {
        //  Get the items on the page
        $rows = (array) $response->json('data', []);

        //  Read the counts from the paginator's meta
        return new Page(
            rows: array_values($rows),
            total: (int) ($response->json('meta.total') ?? count($rows)),
            filtered: $response->json('meta.filtered') === null
                ? null
                : (int) $response->json('meta.filtered'),
        );
    }

    /**
     * Ask the API for a page of rows.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function fetch(PageRequest $page): Page
    {
        //  Build the request parameters
        $params = $this->params($page);

        //  Send the request
        try {
            $response = strtolower($this->method()) === 'post'
                ? $this->client()->post($this->endpoint(), $params)
                : $this->client()->get($this->endpoint(), $params);

            //  An unreachable API is a gateway timeout
        } catch (ConnectionException $exception) {
            throw new RemoteSourceException(504, 'The data source did not respond', $exception);
        }

        //  A failed response is a bad gateway
        if ($response->failed()) {
            throw new RemoteSourceException(502, 'The data source returned an error');
        }

        //  Return the parsed page
        return $this->parse($response);
    }

    /**
     * Return the runner that reads pages from the API.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function runner(): ServerSideRunner
    {
        return new ApiQuery($this);
    }
}
