<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

   


}