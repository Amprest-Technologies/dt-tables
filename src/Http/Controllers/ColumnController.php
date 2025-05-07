<?php

namespace Amprest\LaravelDatatables\Http\Controllers;

use Amprest\LaravelDatatables\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ColumnController extends Controller
{
    /**
     * Initialize the constructor
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function __construct(public Configuration $configuration)
    {
    }

    /**
     * Update a particular table's columns
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $configuration
     * @return \Illuminate\Support\Facades\View
     */
    public function store(Request $request, $configuration)
    {
        //  Find the configuration
        $configuration = $this->configuration->find($configuration);
        $columns = collect($configuration['payload']['columns'])->pluck('name');

        //  Validate the request
        Validator::make($request->all(), [
            'name' => [
                'required',
                'max:30',
                'regex:/^[a-zA-Z0-9_ ]*$/',
                function ($attribute, $value, $fail) use ($columns) {
                    if ($columns->contains(function ($column, $key) use ($value) {
                        $value = Str::slug(strtolower($value), '_');
                        $column = Str::slug(strtolower($column), '_');

                        return $value == $column;
                    })) {
                        $fail('The table column name should be unique');
                    }
                },
            ],
        ], [], [
            'name' => 'column name',
        ])->validate();

        //  Update the payload object
        $payload = $configuration['payload'];
        array_push($payload['columns'], [
            'type' => null,
            'data_type' => 'string',
            'title' => ucwords(strtolower($request->name)),
            'name' => Str::slug(strtolower($request->name), '_'),
            'hidden' => false,
            'sorting' => null,
        ]);

        //  Merge the processed data items
        $this->configuration->update([
            'identifier' => $configuration['identifier'],
            'deleted_at' => $configuration['deleted_at'],
            'payload' => $payload,
        ]);

        //  Redirect to the previous page
        return redirect()->back()->with([
            'success' => 'Columns have been updated successfully.',
        ]);
    }

    /**
     * Update each column
     *
     * @param  \Illuminate\Http\Request  $request
     * @param $configuration
     * @param $column
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function update(Request $request, $configuration, $column)
    {
        //  Get the data object
        $data = $request->merge([
            'title' => ucwords(strtolower($title = $request->title)),
            'name' => Str::slug(strtolower($title), '_'),
        ])->except(['_token', '_method']);

        //  Get the configurations
        $configuration = $this->configuration->find($configuration);

        //  Get the columns
        $columns = collect($configuration['payload']['columns']);

        //  Get the index of the column to be updated
        $index = $columns->search(function ($col) use ($column) {
            return $col['name'] == $column;
        });

        //  Replace the entry with new values
        $configuration['payload']['columns'] = $columns->replace([$index => $data])->toArray();

        //  Update the configurations
        $this->configuration->update($configuration);

        //  Redirect to the configuration index page
        return redirect()->route('datatables.configurations.edit', [
            'configuration' => $configuration['identifier'],
        ])->with([
            'success' => 'The table column has been updated successfully.',
        ]);
    }

    /**
     * Hard delete a tables column
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     *
     * @param  string  $configuration
     * @param  string  $column
     * @return \Illuminate\Support\Facades\View
     */
    public function destroy($configuration, $column)
    {
        //  Find the configuration and permanently delete it
        $configuration = $this->configuration->find($configuration);

        //  Get the payload
        $payload = $configuration['payload'];

        //  Remove any occurrence of the column in the columns
        $payload['columns'] = collect($payload['columns'] ?? [])->filter(function ($option) use ($column) {
            return $option['name'] != $column;
        })->toArray();

        //  Remove any occurrence of the column in the sorting array
        $payload['sorting'] = collect($payload['sorting'] ?? [])->filter(function ($option) use ($column) {
            return $option['column'] != $column;
        })->toArray();

        //  Remove any occurrence of the column in the hidden columns array
        $payload['hiddenColumns'] = array_diff($payload['hiddenColumns'] ?? [], [$column]);

        //  Merge the processed data items
        $this->configuration->update([
            'identifier' => $identifier = $configuration['identifier'],
            'deleted_at' => $configuration['deleted_at'],
            'payload' => $payload,
        ]);

        //  Redirect to the configuration index page
        return redirect()->route('datatables.configurations.edit', [
            'configuration' => $identifier,
        ])->with([
            'success' => 'The table column has been permanently deleted.',
        ]);
    }
}
