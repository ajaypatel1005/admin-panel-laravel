<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ResponseHelper;
use App\Http\Controllers\Helpers\SearchSortPaginationHelper;
use App\Models\ToDo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ToDoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data = ToDo::class;
           
            $records = SearchSortPaginationHelper::applySortSearchPagination($request, $data, ['title', 'description'],['tags']);

            return ResponseHelper::responseMessage('success', $records);
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
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'is_completed' => 'required'
            ],
            [
                'title.required' => 'The title field is required.',
                'title.string' => 'The title must be a valid string.',
                'title.max' => 'The title may not be greater than :max characters.',
                'description.string' => 'The description must be a valid string.',
                'description.max' => 'The description may not be greater than :max characters.',
                'is_completed.required' => 'The completion status is required.'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        DB::beginTransaction();

        try {
            $data = new ToDo();
            $data->title = $request->title;
            $data->description = $request->description;
            $data->is_completed = $request->is_completed;
            $data->created_by = auth()->guard('admin-api')->user()->id;
            $data->save();
            DB::commit();

            if (isset($request->tags)) {
                $tags = [];
                foreach ($request->tags as $tagId) {
                    $tags[$tagId] = [
                        'created_by' => auth()->guard('admin-api')->user()->id,
                        'created_at' => now(),

                    ];
                }
                $data->tags()->attach($tags);
            }


            return ResponseHelper::responseMessage('success', $data, "To Do created successfully.");
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
            $data = ToDo::with('tags')->find($id);

            if (!$data) {
                return ResponseHelper::errorResponse(['ToDo item not found'], 404);
            }

            $data->tags = $data->tags->pluck('id')->toArray();

            // For Return json array of users with the full details
            return ResponseHelper::responseMessage('success', $data);
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ToDo $toDo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|exists:to_dos,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'is_completed' => 'required',
                
            ],
            [
                'id.required' => 'The ToDo ID is required.',
                'id.exists' => 'The ToDo ID does not exist.',
                'title.required' => 'The title field is required.',
                'title.string' => 'The title must be a valid string.',
                'title.max' => 'The title may not be greater than :max characters.',
                'description.string' => 'The description must be a valid string.',
                'description.max' => 'The description may not be greater than :max characters.',
                'is_completed.required' => 'The completion status is required.'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelper::errorResponse($validator->errors()->all(), 422);
        }

        DB::beginTransaction();

        try {
            $data = ToDo::find($id);

            // if (!$data) {
            //     return ResponseHelper::errorResponse(['ToDo item not found'], 404);
            // }

            $data->title = $request->title;
            $data->description = $request->description;
            $data->is_completed = $request->is_completed;
            $data->updated_by = auth()->guard('admin-api')->user()->id;
            $data->save();

            if (isset($request->tags)) {
               
                $data->tags()->sync($request->tags);
            } else {
                $data->tags()->detach();
            }

            DB::commit();

            return ResponseHelper::responseMessage('success', $data, "To Do updated successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(['Something went wrong!!' . $e], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = ToDo::findOrFail($id); // Use findOrFail to handle invalid IDs
            $data->delete();
            DB::commit();
            return ResponseHelper::responseMessage('success', [], "To Do deleted successfully.");
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

            $todos = ToDo::whereIn('id', $ids)->get();

            if ($todos->isEmpty()) {
                DB::rollBack();
                return ResponseHelper::errorResponse(['No ToDos found for the selected records .'], 404);
            }

            $todos->each(function ($todo) {
                $todo->delete();
            });

            DB::commit();
            return ResponseHelper::responseMessage('success', [], "ToDos deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(['Something went wrong!!'], 500);
        }
    }
}
