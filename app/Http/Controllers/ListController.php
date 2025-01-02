<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ResponseHelper;
use App\Models\Tag;
use App\Models\ToDo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{

    public function menu()
    {
        try {
            $user = Auth::guard('admin-api')->user();

            $user->profile_image = env('APP_URL') . '/assets/img/default.png';

            $path = '../app/menu.json';
            $content = json_decode(file_get_contents($path), true);
            $json = file_get_contents('../app/menu.json');
            $json_menu = json_decode($json, true);
            $cacheMenu =  $json_menu['admin'];

            $total_tag = Tag::count();
            $total_todo_complated = ToDo::where('is_completed', 1)->count();
            $total_todo_pending = ToDo::where('is_completed', 0)->count();

            // Calculate total todos
            $total_todo = $total_todo_pending + $total_todo_complated;

            // Calculate percentages
            $total_tag_percentage =  100;
            $total_complated_percentage = ($total_todo > 0) ? ($total_todo_complated / $total_todo) * 100 : 0;
            $total_pending_percentage = ($total_todo > 0) ? ($total_todo_pending / $total_todo) * 100 : 0;

            $total_todo_percentage = ($total_complated_percentage + $total_pending_percentage) / 2;

            $dashboard_data = [
                'total_tag' => number_format((float) $total_tag, 2, '.', ''),
                'total_todo_complated' => number_format((float) $total_todo_complated, 2, '.', ''),
                'total_todo_pending' => number_format((float) $total_todo_pending, 2, '.', ''),
                'total_todo' => number_format((float) $total_todo, 2, '.', ''),
                'total_tag_percentage' => number_format((float) $total_tag_percentage, 2, '.', ''),
                'total_complated_percentage' => number_format((float) $total_complated_percentage, 2, '.', ''),
                'total_pending_percentage' => number_format((float) $total_pending_percentage, 2, '.', ''),
                'total_todo_percentage' => number_format((float) $total_todo_percentage, 2, '.', ''),
            ];

            return ResponseHelper::responseMessage('success', ['menu' => $cacheMenu, 'user' => $user, 'dashboard_data' => $dashboard_data]);
        } catch (Exception $e) {
            return ResponseHelper::errorResponse(['Something went wrong!!']);
        }
    }
}
