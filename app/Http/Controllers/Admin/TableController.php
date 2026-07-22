<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function index()
    {
        $tables = DB::table('tables')->orderBy('number')->get();
        return view('admin.tables.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number',
            'name' => 'nullable|string|max:255',
        ]);
        DB::table('tables')->insert(['number' => $data['number'], 'name' => $data['name'] ?? null, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.tables.index')->with('success','Mesa creada');
    }

    public function destroy($id)
    {
        DB::table('tables')->where('id', $id)->delete();
        return redirect()->route('admin.tables.index')->with('success','Mesa eliminada');
    }
}
