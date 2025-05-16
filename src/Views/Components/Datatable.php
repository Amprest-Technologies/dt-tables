<?php

namespace Amprest\DtTables\Views\Components;

use Amprest\DtTables\Http\Resources\DataTableResource;
use Amprest\DtTables\Models\DataTable as DataTableModel;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DataTable extends Component
{
    /**
     * Initialize a new component instance.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(
        public ?string $id = null,
        public ?string $tableId = null,
        public Collection|array|null $payload = [],
        public ?DataTableResource $table = null,
    )
    {
        //  Set the tableId to the id if not provided
        $this->tableId ??= $this->id;

        //  Handle the payload
        $this->payload = collect(is_null($this->payload) ? [] : $this->payload)->toArray();

        //  Get the table properties
        $this->table = DataTableModel::query()
            ->with('columns')
            ->where('identifier', $this->tableId)
            ->first()
            ->toResource();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.data-table');
    }
}
