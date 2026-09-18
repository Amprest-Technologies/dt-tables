# Button Trigger Events

## What Fires It

The `<x-data-table>` component's inline script listens for the DataTables `buttons-action` event. When the clicked button's `name` is `copy` or `excel` (not `colvis`), it does a client-side `fetch()`:

```js
fetch(route('dt-tables.api.button-triggered'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
        tableId: tableId,
        action: config.name,               // 'copy' or 'excel'
        params: tableParams.buttonTrigger || {},
    }),
});
```

`tableParams` is `payload['parameters']` — whatever the Table class's `parameters()` method returned. No CSRF token is sent; the route is registered under the `api` middleware group, not `web`.

## What Happens Server-Side

`POST /api/dt-tables/button-triggered` (route `dt-tables.api.button-triggered`) is handled by `Amprest\DtTables\Http\Controllers\API\ButtonTriggeredController`, which dispatches:

```php
Amprest\DtTables\Events\DtButtonTriggered::dispatch($request->all());
```

The event's single public property is `$payload`, an array shaped:

```php
[
    'tableId' => 'invoices-table',      // the <x-data-table> id
    'action' => 'excel',                // 'copy' or 'excel'
    'params' => [ /* your buttonTrigger data */ ],
]
```

## Listening For It

Register a listener for `Amprest\DtTables\Events\DtButtonTriggered` — Laravel auto-discovers a `handle(DtButtonTriggered $event)` method on any class in `app/Listeners`, no manual registration needed:

```php
<?php

namespace App\Listeners;

use Amprest\DtTables\Events\DtButtonTriggered;

class LogTableExport
{
    public function handle(DtButtonTriggered $event): void
    {
        $tableId = $event->payload['tableId'];
        $action = $event->payload['action'];
        $params = $event->payload['params'];

        // e.g. write an activity log entry using $params['activityLog']
    }
}
```

## Wiring Up `params`

`params` is whatever you return from the Table class's `parameters()` method under the `buttonTrigger` key — this is the mechanism for passing context (who exported, what filters were active) through to the listener:

```php
protected function parameters(): array
{
    return [
        'buttonTrigger' => [
            'activityLog' => [
                'userId' => user()->id,
                'event' => 'invoices-table-exported',
                'subject' => ['model' => Invoice::class, 'id' => $this->organization->id],
            ],
        ],
    ];
}
```

## Common Pitfalls

- Only `copy` and `excel` button clicks fire the event — `colvis` (column visibility) does not.
- If `parameters()` returns no `buttonTrigger` key, the listener receives `params: []` — always check before indexing into it.
- The event fires once per button click, not once per exported row — it's meant for activity logging, not per-row processing.
- Nothing dispatches automatically if a table has no `copy`/`excel` buttons enabled in its `settings.buttons` — check the table's config if a listener never fires.
