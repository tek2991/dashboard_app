<?php

namespace App\Http\Controllers;

use App\Models\Mo;
use App\Models\Set;
use App\Models\Office;
use Auth;
use Illuminate\Http\Request;

class MoController extends Controller
{
    // Register MoPolicy
    public function __construct()
    {
        $this->authorizeResource(Mo::class, 'mo');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('mos.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sets = Set::all();
        // Get all offices of the logged in user
        $offices = auth()->user()->offices;
        $int_fields = ['bags_opening_balance', 'bags_received', 'bags_opened', 'bags_closed', 'bags_dispatched', 'bags_transferred', 'articles_received', 'articles_closed', 'articles_pending', 'customs_examination', 'customs_clearance', 'customs_pending', 'sa_WS', 'mts_WS', 'dwl_WS', 'gds_WS'];
        $boolean_fields = [
            'manpower' => 'Man Power as per Est norms achieved',
            'logbook' => 'RTN/MMS logbook properly maintained',
            'rtn' => 'RTN/MMS ontime arrival & departure',
        ];
        return view('mos.create', compact('sets', 'offices', 'int_fields', 'boolean_fields'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $int_fields = ['bags_opening_balance', 'bags_received', 'bags_opened', 'bags_closed', 'bags_dispatched', 'bags_transferred', 'articles_received', 'articles_closed', 'articles_pending', 'customs_examination', 'customs_clearance', 'customs_pending', 'sa_WS', 'mts_WS', 'dwl_WS', 'gds_WS'];
        $boolean_fields = [
            'manpower' => 'Man Power as per Est norms achieved',
            'logbook' => 'RTN/MMS logbook properly maintained',
            'rtn' => 'RTN/MMS ontime arrival & departure',
        ];

        // validate int_fields from request
        $validated = [];
        foreach ($int_fields as $field) {
            $request->validate([$field => 'integer']);
            // Add to validated array
            $validated[$field] = $request->$field;
        }

        // validate boolean_fields from request
        foreach ($boolean_fields as $field => $label) {
            $request->validate([$field => 'boolean',]);
            // Add to validated array
            $validated[$field] = $request->$field;
        }

        $validated = array_merge($validated, $this->validate($request, [
            'date' => 'required|date',
            'set_id' => 'required|integer|exists:sets,id',
            'remarks' => 'nullable|string|max:4096',
            'office_id' => 'required|integer|exists:offices,id',
        ]));

        $validated['user_id'] = Auth::user()->id;

        Mo::create($validated);

        return redirect()->route('mos.create')->with('success', 'Data added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mo  $mo
     * @return \Illuminate\Http\Response
     */
    public function show(Mo $mo)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mo  $mo
     * @return \Illuminate\Http\Response
     */
    public function edit(Mo $mo)
    {
        $sets = Set::all();
        $offices = Office::all();
        $int_fields = ['bags_opening_balance', 'bags_received', 'bags_opened', 'bags_closed', 'bags_dispatched', 'bags_transferred', 'articles_received', 'articles_closed', 'articles_pending', 'customs_examination', 'customs_clearance', 'customs_pending', 'sa_WS', 'mts_WS', 'dwl_WS', 'gds_WS'];
        $boolean_fields = [
            'manpower' => 'Man Power as per Est norms achieved',
            'logbook' => 'RTN/MMS logbook properly maintained',
            'rtn' => 'RTN/MMS ontime arrival & departure',
        ];
        return view('mos.edit', compact('mo', 'sets', 'offices', 'int_fields', 'boolean_fields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mo  $mo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mo $mo)
    {
        $int_fields = ['bags_opening_balance', 'bags_received', 'bags_opened', 'bags_closed', 'bags_dispatched', 'bags_transferred', 'articles_received', 'articles_closed', 'articles_pending', 'customs_examination', 'customs_clearance', 'customs_pending', 'sa_WS', 'mts_WS', 'dwl_WS', 'gds_WS'];
        $boolean_fields = [
            'manpower' => 'Man Power as per Est norms achieved',
            'logbook' => 'RTN/MMS logbook properly maintained',
            'rtn' => 'RTN/MMS ontime arrival & departure',
        ];

        // validate int_fields from request
        $validated = [];
        foreach ($int_fields as $field) {
            $request->validate([$field => 'integer']);
            // Add to validated array
            $validated[$field] = $request->$field;
        }

        // validate boolean_fields from request
        foreach ($boolean_fields as $field => $label) {
            $request->validate([$field => 'boolean',]);
            // Add to validated array
            $validated[$field] = $request->$field;
        }

        $validated = array_merge($validated, $this->validate($request, [
            'date' => 'required|date',
            'set_id' => 'required|integer|exists:sets,id',
            'remarks' => 'nullable|string|max:4096',
            'office_id' => 'required|integer|exists:offices,id',
        ]));

        $validated['user_id'] = Auth::user()->id;

        $mo->update($validated);
        return redirect()->route('mos.index')->with('success', 'Data updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mo  $mo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mo $mo)
    {
        //
    }
}
