<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ResponseHelper;
use App\Http\Controllers\Helpers\SearchSortPaginationHelper;
use App\Models\Tag;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data = Tag::class;
            $records = SearchSortPaginationHelper::applySortSearchPagination($request, $data, ['name'],[]);

            return ResponseHelper::responseMessage('success', $records);
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }

    public function allTags(Request $request)
    {
        try {
            $data = Tag::get();
           
            return ResponseHelper::responseMessage('success', $data);
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
            ],
            [
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name may not be greater than :max characters.',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        DB::beginTransaction();

        try {
            $data = new Tag();
            $data->name = $request->name;
            $data->created_by = auth()->guard('admin-api')->user()->id;
            $data->save();
            DB::commit();

            return ResponseHelper::responseMessage('success', $data, "Tag created successfully.");
        } catch (Exception $e) {
            DB::rollBack();

            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // For gettting signle record
            $data = Tag::find($id);

            if (!$data) {
                return ResponseHelper::errorResponse(['Tag item not found'], 404);
            }

            // For Return json array of users with the full details
            return ResponseHelper::responseMessage('success', $data);
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,string $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|exists:tags,id',
                'name' => 'required|string|max:255',
               
            ],
            [
                'id.required' => 'The Tag ID is required.',
                'id.exists' => 'The Tag ID does not exist.',
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The title may not be greater than :max characters.',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        DB::beginTransaction();

        try {
            $data = Tag::find($id);

            // if (!$data) {
            //     return ResponseHelper::errorResponse(['Tag is not found'], 404);
            // }

            $data->name = $request->name;
            $data->updated_by = auth()->guard('admin-api')->user()->id;
            $data->save();

            DB::commit();

            return ResponseHelper::responseMessage('success', $data, "Tag updated successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Tag::findOrFail($id); // Use findOrFail to handle invalid IDs
            $data->delete();
            DB::commit();
            return ResponseHelper::responseMessage('success', [], "Tag deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();

            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }

    /**
     * Remove the all resource from storage.
     *
     * */
    public function deleteAll(Request $request)
    {
        DB::beginTransaction();

        try {
            $ids = explode(",", $request->delete_ids);

            $todos = Tag::whereIn('id', $ids)->get();

            if ($todos->isEmpty()) {
                DB::rollBack();
                return ResponseHelper::errorResponse(['No Tag found for the selected records .'], 404);
            }

            $todos->each(function ($todo) {
                $todo->delete();
            });

            DB::commit();
            return ResponseHelper::responseMessage('success', [], "Tag deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }
}
