<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkPlace;
use Illuminate\Http\Request;

class WorkPlaceController extends Controller
{
    public function index()
    {
        $workPlaces = WorkPlace::orderBy('code')->paginate(10);
        return view('admin.work_places.index', compact('workPlaces'));
    }

    public function create()
    {
        return view('admin.work_places.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:work_places,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        WorkPlace::create($data);
        return redirect()->route('admin.work_places.index');
    }

    public function edit(WorkPlace $workPlace)
    {
        return view('admin.work_places.edit', compact('workPlace'));
    }

    public function update(Request $request, WorkPlace $workPlace)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:work_places,code,'.$workPlace->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $workPlace->update($data);
        return redirect()->route('admin.work_places.index');
    }

    public function destroy(WorkPlace $workPlace)
    {
        $workPlace->delete();
        return redirect()->route('admin.work_places.index');
    }
}
