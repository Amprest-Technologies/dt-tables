@extends('docs::layout')

@section('page', 'Export Events')
@section('lead', 'React when someone copies or exports a table.')

@section('content')
    <div class="docs-prose mb-10">
<p>
    When someone clicks <strong>Copy</strong> or <strong>Excel</strong> on an inline table, the package tells your app, so you can record who exported what.
    The <strong>Hide Columns</strong> button never fires it.
</p>

<x-docs::callout>
    Server-side and API tables have no Copy or Excel buttons, because an export could only see the page that is loaded. The event fires for inline tables only.
</x-docs::callout>
    </div>

    <x-docs::section id="what-is-sent" title="What is sent">
<p>The browser posts to <code>/api/dt-tables/button-triggered</code>, and the package dispatches <code>Nyumbani\DtTables\Events\DtButtonTriggered</code> with this payload:</p>

<x-docs::code lang="php">
@verbatim
[
    'tableId' => 'invoices-table',   // the id of the table
    'action' => 'excel',             // 'copy' or 'excel'
    'params' => [],                  // whatever your table class put under 'buttonTrigger'
]
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="passing-context-to-the-listener" title="Passing context to the listener">
<p>
    Whatever your table class returns from <code>parameters()</code> under <code>buttonTrigger</code> arrives as <code>params</code>.
    Use it to say who exported the table and what it contained.
</p>

<x-docs::code lang="php" title="In the table class">
@verbatim
use App\Models\Invoice;

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
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="listening-for-the-event" title="Listening for the event">
<p>Laravel finds a <code>handle()</code> method that accepts the event in <code>app/Listeners</code>, so there is nothing to register.</p>

<x-docs::code lang="php" title="app/Listeners/LogTableExport.php">
@verbatim
&lt;?php

namespace App\Listeners;

use Nyumbani\DtTables\Events\DtButtonTriggered;

class LogTableExport
{
    public function handle(DtButtonTriggered $event): void
    {
        $log = $event->payload['params']['activityLog'] ?? null;

        if (! $log) {
            return;
        }

        logger()->info($log['event'], ['user' => $log['userId'], 'action' => $event->payload['action']]);
    }
}
@endverbatim
</x-docs::code>
    </x-docs::section>

    <x-docs::section id="good-to-know" title="Good to know">
<ul>
    <li>The event fires once per click, not once per row. It is meant for activity logs.</li>
    <li>If <code>parameters()</code> has no <code>buttonTrigger</code> key, <code>params</code> is an empty array, so check before you read it.</li>
    <li>The route belongs to the <code>api</code> middleware group, so it needs no CSRF token. Do not put anything secret in <code>params</code>, because the browser sends it back.</li>
</ul>
    </x-docs::section>
@endsection
