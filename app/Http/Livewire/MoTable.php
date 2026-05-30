<?php

namespace App\Http\Livewire;

use App\Models\Mo;
use App\Models\Office;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\{Button, Column, Footer, Header, PowerGridComponent};

final class MoTable extends PowerGridComponent
{
    use WithExport;
    public string $tableName = 'tbl_motable';


    public string $primaryKey = 'mos.id';

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(50)
                ->showRecordCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Datasource
    |--------------------------------------------------------------------------
    | Provides data to your Table using a Model or Collection
    |
    */

    /**
     * PowerGrid datasource.
     *
     * @return Builder<\App\Models\Mo>
     */
    public function datasource(): Builder
    {
        $query =  Mo::query()
            ->join('offices', 'offices.id', '=', 'mos.office_id')
            ->join('sets', 'sets.id', '=', 'mos.set_id')
            ->select('mos.*', 'offices.name as office_name', 'sets.name as set_name')
            ->orderBy('mos.date', 'desc');

        // Check if user has any of the roles: 'Administrator', 'Verified'
        if (!auth()->user()->hasRole(['Administrator', 'Verified'])) {
            $assigned_offices = auth()->user()->offices->pluck('id')->toArray();
            $query = $query->whereIn('mos.office_id', $assigned_offices);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    |  Relationship Search
    |--------------------------------------------------------------------------
    | Configure here relationships to be used by the Search and Table Filters.
    |
    */

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [
            'office' => ['name'],
            'set' => ['name'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Add Column
    |--------------------------------------------------------------------------
    | Make Datasource fields available to be used as columns.
    | You can pass a closure to transform/modify the data.
    |
    */
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('office_name')
            ->add('date')
            ->add('date_formatted', function (Mo $model) {
                return $model->date->format('d/m/y');
            })
            ->add('set_name')

            ->add('bags_opening_balance')
            ->add('bags_received')
            ->add('bags_opened')
            ->add('bags_closed')
            ->add('bags_dispatched')
            ->add('bags_transferred')
            ->add('bags', function (Mo $model) {
                return $model->bags_opening_balance . '/' . $model->bags_received . '/' . $model->bags_opened . '/' . $model->bags_closed . '/' . $model->bags_dispatched . '/' . $model->bags_transferred;
            })

            ->add('articles_received')
            ->add('articles_closed')
            ->add('articles_pending')

            ->add('customs_examination')
            ->add('customs_clearance')
            ->add('customs_pending')

            ->add('sa_WS')
            ->add('mts_WS')
            ->add('dwl_WS')
            ->add('gds_WS')

            ->add('manpower')
            ->add('manpower_formatted', function (Mo $model) {
                return $model->manpower ? 'Yes' : 'No';
            })
            ->add('logbook')
            ->add('logbook_formatted', function (Mo $model) {
                return $model->logbook ? 'Yes' : 'No';
            })
            ->add('rtn')
            ->add('rtn_formatted', function (Mo $model) {
                return $model->rtn ? 'Yes' : 'No';
            })

            ->add('remarks')

            ->add('created_at')
            ->add('created_at_formatted', fn (Mo $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    /*
    |--------------------------------------------------------------------------
    |  Include Columns
    |--------------------------------------------------------------------------
    | Include the columns added columns, making them visible on the Table.
    | Each column can be configured with properties, filters, actions...
    |
    */

    /**
     * PowerGrid Columns.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('Office', 'office_name'),

            Column::make('Date', 'date_formatted', 'date')
                ->searchable(),

            Column::make('Set', 'set_name'),

            Column::make('Bags Op_Bal', 'bags_opening_balance'),
            Column::make('Bags Rec', 'bags_received'),
            Column::make('Bags Op', 'bags_opened'),
            Column::make('Bags Cl', 'bags_closed'),
            Column::make('Bags Disp', 'bags_dispatched'),
            // Column::make('Bags Rec/Op/Cl/Disp', 'bags')
            // ->sortable(),
            Column::make('Bags Tnf', 'bags_transferred'),

            Column::make('Art Rec', 'articles_received'),
            Column::make('Art Cl', 'articles_closed'),
            Column::make('Art Pd', 'articles_pending'),

            Column::make('Cust Exam', 'customs_examination'),
            Column::make('Cust Clear', 'customs_clearance'),
            Column::make('Cust Pd', 'customs_pending'),

            Column::make('SA', 'sa_WS'),
            Column::make('MTS', 'mts_WS'),
            Column::make('DWL', 'dwl_WS'),
            Column::make('GDS', 'gds_WS'),

            // Column::make('Manpower', 'manpower_formatted'),
            // Column::make('Logbook', 'logbook_formatted'),
            // Column::make('RTN', 'rtn_formatted'),

            Column::make('Remarks', 'remarks'),


            Column::make('Created at', 'created_at')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->searchable()
                ->hidden(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Facades\Filter::select('office_name', 'office_id')
                ->dataSource(Office::query()->orderBy('name')->get())
                ->optionLabel('name')
                ->optionValue('id'),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::datepicker('date_formatted', 'date'),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::datepicker('created_at_formatted', 'created_at'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

    /**
     * PowerGrid Mo Action Buttons.
     *
     * @return array<int, Button>
     */

    public function actions($row): array
    {
        return [
            Button::make('edit', 'Edit')
                ->class('text-indigo-600 hover:text-indigo-900 hover:underline')
                ->route('mos.edit', ['mo' => $row->id])

            /*
           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('mo.destroy', ['mo' => $row->id])
               ->method('delete')
               */
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * PowerGrid Mo Action Rules.
     *
     * @return array<int, RuleActions>
     */


    public function actionRules(): array
    {
        return [
            //Check if user is owner of the Mo
            Rule::button('edit')
                ->when(fn (Mo $mo) => $mo->user_id !== auth()->id())
                ->hide(),
        ];
    }
}
