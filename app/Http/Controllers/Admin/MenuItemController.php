<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Models\MenuItem;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $menuFilter = $request->query('menu');
        $query = MenuItem::with('menu');
        if ($menuFilter) {
            $query->where('menuID', $menuFilter);
        }
        $items = $query->get();
        // Provide today's exchange rate (Bs per 1 USD) to the view when available
        $today = now()->toDateString();
        $exchangeRate = \App\Models\ExchangeRate::where('date', '<=', $today)->orderBy('date','desc')->value('rate');
        return view('admin.menuitem.index', compact('items', 'menuFilter', 'exchangeRate'));
    }

    public function create()
    {
        $menus = Menu::all();
        return view('admin.menuitem.create', compact('menus'));
    }

    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();
        MenuItem::create($data);
        return redirect()->route('admin.menuitem.index')->with('success','Item creado');
    }

    public function edit($id)
    {
        $item = MenuItem::findOrFail($id);
        $menus = Menu::all();
        return view('admin.menuitem.edit', compact('item','menus'));
    }

    public function update(StoreMenuItemRequest $request, $id)
    {
        $item = MenuItem::findOrFail($id);
        $item->update($request->validated());
        return redirect()->route('admin.menuitem.index')->with('success','Item actualizado');
    }

    public function destroy($id)
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.menuitem.index')->with('success','Item eliminado');
    }

    public function updatePrice(Request $request, $id)
    {
        $data = $request->validate([
            'price' => ['required','numeric','min:0']
        ]);
        $item = MenuItem::findOrFail($id);
        $item->price = $data['price'];
        $item->save();
        return response()->json(['status' => 'ok','price' => $item->price]);
    }
}
