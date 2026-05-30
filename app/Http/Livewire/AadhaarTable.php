<?php

namespace App\Http\Livewire;

use App\Models\Aadhaar;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\{Button, Column, Footer, Header, PowerGridComponent};

final class AadhaarTable extends PowerGridComponent
{
    use WithExport;
    public string $tableName = 'tbl_aadhaartable';


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
                ->showPerPage()
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
    * @return Builder<\App\Models\Aadhaar>
    */
    public function datasource(): Builder
    {
        return Aadhaar::query()
        ->join('imports', 'imports.id', '=', 'aadhaars.import_id')
        ->join('divisions', 'divisions.id', '=', 'aadhaars.division_id')
        ->join('pincodes', 'pincodes.id', '=', 'aadhaars.pincode_id')
        ->select('aadhaars.*', 'imports.file_name', 'divisions.name as division_name', 'pincodes.pincode as pincode');
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
        return [];
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
            ->add('id')
            ->add('import_id')
            ->add('division_id')
            ->add('division_name')
            ->add('station_no')
            ->add('centre_name')
            ->add('pincode')
            ->add('operator_name')
            ->add('transaction_date')
            ->add('transaction_date_formatted', fn (Aadhaar $model) => Carbon::parse($model->transaction_date)->format('d/m/Y'))
            ->add('centre_type')
            ->add('enrolments')
            ->add('updates')
            ->add('created_at_formatted', fn (Aadhaar $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Aadhaar $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
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
            Column::make('DIVISION', 'division_name'),

            Column::make('STATION NO', 'station_no')
                ->sortable(),

            Column::make('CENTRE NAME', 'centre_name')
                ->sortable()
                ->searchable(),

            Column::make('OPERATOR NAME', 'operator_name')
                ->sortable()
                ->searchable(),

            Column::make('TRANSACTION DATE', 'transaction_date_formatted', 'transaction_date')
                ->searchable()
                ->sortable(),

            Column::make('CENTRE TYPE', 'centre_type')
                ->sortable(),

            Column::make('ENROLMENTS', 'enrolments')
                ->sortable(),
            Column::make('UPDATES', 'updates')
                ->sortable(),

            Column::make('CREATED AT', 'created_at_formatted', 'created_at')
                ->hidden()
                ->visibleInExport(True),

        ]
;
    }

    public function filters(): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Facades\Filter::select('division_name', 'aadhaars.division_id')
                ->dataSource(\App\Models\Division::all())
                ->optionLabel('name')
                ->optionValue('id'),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::inputText('centre_name'),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::inputText('operator_name'),
            \PowerComponents\LivewirePowerGrid\Facades\Filter::datepicker('transaction_date_formatted', 'transaction_date'),
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
     * PowerGrid Aadhaar Action Buttons.
     *
     * @return array<int, Button>
     */

    /*
    public function actions($row): array
    {
       return [
           Button::make('edit', 'Edit')
               ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
               ->route('aadhaar.edit', ['aadhaar' => $row->id]),

           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('aadhaar.destroy', ['aadhaar' => $row->id])
               ->method('delete')
        ];
    }
    */

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

     /**
     * PowerGrid Aadhaar Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($aadhaar) => $aadhaar->id === 1)
                ->hide(),
        ];
    }
    */
}
