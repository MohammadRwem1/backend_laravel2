<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apartment;
use Illuminate\Support\Facades\Auth;

class ApartmentController extends Controller
{
    // عرض جميع الشقق مع فلترة
    public function index(Request $request)
    {
        $query = Apartment::query();

        if ($request->filled('governorate')) {
            $query->where('governorate', $request->governorate);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('number_rooms')) {
            $query->where('number_rooms', $request->number_rooms);
        }

        $apartments = $query->select(
            'id',
            'title',
            'governorate',
            'city',
            'price',
            'main_image'
        )->get();

        $apartments->transform(function ($apartment) {
            $apartment->main_image = $apartment->main_image ? url($apartment->main_image) : null;
            return $apartment;
        });

        return response()->json([
            'status' => true,
            'data' => $apartments
        ]);
    }

    public function show($id)
    {
        $apartment = Apartment::with(['owner', 'images'])->find($id);

        if (!$apartment) {
            return response()->json([
                'status' => false,
                'message' => 'Apartment not found'
            ], 404);
        }

        $images = $apartment->images->map(function ($img) {
            return [
                'id' => $img->id,
                'image_path' => $img->image_path ? url($img->image_path) : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'id'           => $apartment->id,
                'title'        => $apartment->title,
                'price'        => $apartment->price,
                'description'  => $apartment->description,
                'city'         => $apartment->city,
                'governorate'  => $apartment->governorate,
                'number_rooms' => $apartment->number_rooms,
                'owner_id'     => $apartment->owner_id,
                'owner_name'   => $apartment->owner->name ?? null,
                'main_image'   => $apartment->main_image ? url($apartment->main_image) : null,
                'images'       => $images,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'governorate'  => 'required|string',
            'city'         => 'required|string',
            'number_rooms' => 'required|numeric|between:1,5',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'main_image'   => 'nullable|file|image',
            'images'       => 'nullable|array',
            'images.*'     => 'file|image',
        ]);

        $mainPath = null;

        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(base_path('uploads/apartments/main'), $filename);
            $mainPath = 'uploads/apartments/main/' . $filename;
        }

        $apartment = Apartment::create([
            'title'        => $request->title,
            'description'  => $request->description,
            'price'        => $request->price,
            'governorate'  => $request->governorate,
            'city'         => $request->city,
            'number_rooms' => $request->number_rooms,
            'owner_id'     => $request->user()->id,
            'main_image'   => $mainPath,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(base_path('uploads/apartments/images'), $filename);

                $apartment->images()->create([
                    'image_path' => 'uploads/apartments/images/' . $filename
                ]);
            }
        }

        $apartment->load('images');
        $apartment->main_image = $apartment->main_image ? url($apartment->main_image) : null;
        $apartment->images->transform(function ($img) {
            $img->image_path = $img->image_path ? url($img->image_path) : null;
            return $img;
        });

        return response()->json($apartment, 201);
    }

    public function update(Request $request, Apartment $apartment)
    {
        $this->authorize('update', $apartment);

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric',
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
