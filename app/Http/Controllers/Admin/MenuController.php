<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return view('admin.menu.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menu.create');
    }

    public function store(StoreMenuRequest $request)
    {
        $data = $request->validated();
    Menu::create([ 'menuName' => $data['menuName'] ]);
    return redirect()->route('admin.menu.index')->with('success', 'Menu creado');
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        return view('admin.menu.edit', compact('menu'));
    }

    public function update(StoreMenuRequest $request, $id)
    {
        $menu = Menu::findOrFail($id);
    $menu->update(['menuName' => $request->input('menuName')]);
    return redirect()->route('admin.menu.index')->with('success', 'Menu actualizado');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
    $menu->delete();
    return redirect()->route('admin.menu.index')->with('success', 'Menu eliminado');
    }
}
