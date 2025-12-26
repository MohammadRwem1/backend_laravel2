<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apartment;
use Illuminate\Support\Facades\Auth;


class ApartmentController extends Controller
{
    public function index()
{
    $apartments = Apartment::select(
        'id',
        'title',
        'governorate',
        'city',
        'price',
        'main_image'
    )->get();

    return response()->json([
        'status' => true,
        'data' => $apartments
    ]);
}

public function show($id)
{
    $apartment = Apartment::find($id);

    if (!$apartment) {
        return response()->json([
            'status' => false,
            'message' => 'Apartment not found'
        ], 404);
    }

        return response()->json([
        'status' => true,
        'data' => [
            'id'          => $apartment->id,
            'title'       => $apartment->title,
            'location'    => $apartment->location,
            'price'       => $apartment->price,
            'description' => $apartment->description,
            'main_image'  => $apartment->main_image,
            'images'      => json_decode($apartment->images, true)
        ]
    ]);
}

public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'governorate' => 'required|string',
            'city'        => 'required|string',
            'number_rooms'=> 'required|numeric|between:1,5',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'main_image'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $apartment = Apartment::create([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'owner_id'    => Auth::id(),
        ]);

        return response()->json($apartment, 201);
    }

    public function update(Request $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $request->validate([
            'title'       => 'string|max:255',
            'description' => 'string|nullable',
            'price'       => 'numeric|nullable',
        ]);

        $apartment->update($request->only('title', 'description', 'price'));

        return response()->json($apartment, 200);
    }

    public function destroy(Apartment $apartment)
    {
        $this->authorize('delete', $apartment);

        $apartment->delete();

        return response()->json(['message' => 'Apartment deleted'], 200);
    }

}
