<?php

namespace App\Http\Livewire;

use App\Models\Byod;
use App\Models\Division;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\{Button, Column, Footer, Header, PowerGridComponent};

final class ByodTable extends PowerGridComponent
{
    use WithExport;
    public string $tableName = 'tbl_byodtable';


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
     * @return Builder<\App\Models\Byod>
     */
    public function datasource(): Builder
    {
        return Byod::query()
            ->join('divisions', 'divisions.id', '=', 'byods.division_id')
            ->join('users', 'users.id', '=', 'byods.created_by')
            ->select('byods.*', 'divisions.name as division_name', 'users.name as created_by_name');
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
            ->add('name')
            ->add('mobile')
            ->add('email')
            ->add('make_model')
            ->add('imei')
            ->add('post_office')
            ->add('date_of_purchase_formatted', fn (Byod $model) => Carbon::parse($model->date_of_purchase)->format('d/m/Y'))
            ->add('date_of_acceptance_formatted', fn (Byod $model) => Carbon::parse($model->date_of_acceptance)->format('d/m/Y'))
            ->add('division_id')
            ->add('division_name')
            ->add('employee_id')
            ->add('created_by')
            ->add('created_by_name')
            ->add('created_at_formatted', fn (Byod $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->add('updated_at_formatted', fn (Byod $model) => Carbon::parse($model->updated_at)->format('d/m/Y H:i:s'));
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
            Column::make('NAME', 'name')
                ->sortable()
                ->searchable(),

            Column::make('MOBILE', 'mobile')
                ->sortable()
                ->searchable(),

            Column::make('EMAIL', 'email')
                ->sortable()
                ->searchable(),

            Column::make('MAKE MODEL', 'make_model')
                ->sortable()
                ->searchable(),

            Column::make('IMEI', 'imei')
                ->sortable()
                ->searchable(),

            Column::make('POST OFFICE', 'post_office')
                ->sortable()
                ->searchable(),

            Column::make('DATE OF PURCHASE', 'date_of_purchase_formatted', 'date_of_purchase')
                ->searchable()
                ->sortable(),

            Column::make('DATE OF ACCEPTANCE', 'date_of_acceptance_formatted', 'date_of_acceptance')
                ->searchable()
                ->sortable(),

            Column::make('DIVISION ID', 'division_name', 'division_id'),

            Column::make('EMPLOYEE ID', 'employee_id')
                ->sortable()
                ->searchable(),

            Column::make('CREATED BY', 'created_by_name', 'created_by')
                ->sortable(),

            Column::make('CREATED AT', 'created_at_formatted', 'created_at')
                ->searchable()
                ->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Facades\Filter::select('division_name', 'division_id')
                ->dataSource(\App\Models\Division::all())
                ->optionLabel('name')
                ->optionValue('id'),
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
     * PowerGrid Byod Action Buttons.
     *
     * @return array<int, Button>
     */


    public function actions($row): array
    {
        return [
            Button::make('edit', 'Edit')
                ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
                ->route('byods.edit', ['byod' => $row->id]),

            //    Button::make('destroy', 'Delete')
            //        ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
            //        ->route('byod.destroy', ['byod' => $row->id])
            //        ->method('delete')
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
     * PowerGrid Byod Action Rules.
     *
     * @return array<int, RuleActions>
     */


    public function actionRules(): array
    {
        return [

            //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn (Byod $model) => $model->created_by !== auth()->id())
                ->hide(),
        ];
    }
}
