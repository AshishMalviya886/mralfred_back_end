<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Validators\PostValidator;

use App\Traits\ApiResponse;

use App\Models\User;
use App\Models\Post;

use DB;
use GuzzleHttp;

use Config;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use stdClass;

use App\Http\Resources\Post as PostResource;
use App\Http\Resources\PostCollection;


class PostController extends Controller
{
    use ApiResponse;

    protected $instance = null;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth:api')->only(['logout','refreshToken']);
    }

     // Get all posts
    public function index(Request $request)
    {

        $user = $request->user();
        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page') ?? 1;

        
        $posts = $user->posts();        

        $paginateData = $posts->paginate($perPage);
        $collection = new PostCollection($paginateData->getCollection());
        $metaInfo = $paginateData->toArray();
        unset($metaInfo['data']);
        return $this->successResponse(['data' => $collection, 'meta' => $metaInfo]);

    }

    public function store(Request $request, PostValidator $postValidator)
    {

    
    try {

        DB::beginTransaction();

        $user = $request->user();
        $input = $request->all();


        if (!$postValidator->with($input)->passes()) {
            return $this->failResponse([
                "message" => $postValidator->getErrors()[0],
                "messages" => $postValidator->getErrors()
            ], 422);
        }
        

        $param =  [
            'user_id' => $user->id,
            'title' => $input['title'],
            'description' => $input['description']                      
        ];

        $post = Post::create($param);
        DB::commit();
        return $this->successResponse(['message' => trans('message.post_added'), 'data' => new PostResource($post)]);

    } catch (\Exception $e) {
        DB::rollback();
        return $this->failResponse([
            "message" => $e->getMessage(),
        ], 500);
    }



    }

    public function show(Request $request, $id)
    {

    
    try {
        $user = $request->user();
        $input = $request->all();

        $post = $user->posts()->findOrFail($id);

        return $this->successResponse(['message' => trans('message.post_get'), 'data' => new PostResource($post)]);

    } catch (\Exception $e) {
        return $this->failResponse([
            "message" => $e->getMessage(),
        ], 500);
    }

    }

    public function update(Request $request, $id)
    {

    
    try {

        DB::beginTransaction();

        $user = $request->user();
        $input = $request->all();

        $post = $user->posts()->findOrFail($id);

        $postValidator = new PostValidator(); 

        if (!$postValidator->with($input)->passes()) {
            return $this->failResponse([
                "message" => $postValidator->getErrors()[0],
                "messages" => $postValidator->getErrors()
            ], 422);
        }
        

        $post->title = $input['title'];
        $post->description = $input['description'];
            
        $post->save();             
        DB::commit();


        return $this->successResponse(['message' => trans('message.post_updated'), 'data' => new PostResource($post)]);

    } catch (\Exception $e) {
        DB::rollback();
        return $this->failResponse([
            "message" => $e->getMessage(),
        ], 500);
    }

    }

}