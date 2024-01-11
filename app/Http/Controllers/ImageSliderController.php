<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageSliderRequest;
use App\Http\Requests\UpdateImageSliderRequest;
use App\Models\ImageSlider;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;

class ImageSliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $imageSliders = ImageSlider::all();
        return $this->Res($imageSliders, 'All Image Sliders');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageSliderRequest $request, ImageSlider $imageSlider): JsonResponse
    {
        // Upload the image to Cloudinary
        $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'FurnitureStore'
        ])->getSecurePath();

        $imageSlider->imageUrl = $imageUrl;
        $imageSlider->save();

        return $this->Res($imageSlider, 'Image Slider created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ImageSlider $imageSlider): JsonResponse
    {
        return $this->Res($imageSlider, 'Image Slider');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImageSliderRequest $request, ImageSlider $imageSlider): JsonResponse
    {
        // Upload the image to Cloudinary
        $imageUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'FurnitureStore'
        ])->getSecurePath();

        $imageSlider->imageUrl = $imageUrl;
        $imageSlider->save();

        return $this->Res($imageSlider, 'Image Slider updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImageSlider $imageSlider): JsonResponse
    {
        $imageSlider->delete();
        return $this->Res(null, 'Image Slider deleted successfully');
    }
}
