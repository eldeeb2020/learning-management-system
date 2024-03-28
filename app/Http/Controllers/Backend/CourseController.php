<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Course;
use App\Models\Course_goal;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function AllCourse(){

        $id = Auth::user()->id;
        $courses = Course::where('instructor_id' , $id)->orderBy('id', 'desc')->get();

        return view('instructor.courses.all_course', compact('courses'));

    } //End Method

    public function AddCourse(){

        $categories = Category::latest()->get();
        return view('instructor.courses.add_course' , compact('categories'));


    } // End Method


    public function GetSubCategory($category_id){

        $subcat = SubCategory::where('category_id' , $category_id)->orderBy('subcategory_name', 'ASC')->get(); 

        return json_encode($subcat);


    } // end method

    public function StoreCourse(Request $request){


        $request->validate(['video' => 'required|mimes:mp4|max:10000',]);



        if ($request->file('course_image') && $request->file('video')){
            $manager = new ImageManager (new Driver());
            $name_gen = hexdec(uniqid()).'.'.$request->file('course_image')->getClientOriginalExtension();
            $img = $manager->read($request->file('course_image'));
            $img = $img->resize(370,246);

            $img->toJpeg(80)->save(base_path('public/upload/course/thambnail/'.$name_gen));
            $save_url = 'upload/course/thambnail/'.$name_gen;


            $video = $request->file('video');
            $videoName = time().'.'.$video->getClientOriginalExtension();
            $video->move(public_path('upload/course/video'), $videoName);
            $save_video = 'upload/course/video/'.$videoName;


            $course_id = Course::insertGetId([

                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'instructor_id' => Auth::user()->id,
                'course_title' => $request->course_title,
                'course_name' => $request->course_name,
                'course_name_slug' => strtolower(str_replace(' ', '-', $request->course_name)),
                'description' => $request->description,
                'video' => $save_video,
    
                'label' => $request->label,
                'duration' => $request->duration,
                'resources' => $request->resources,
                'certificate' => $request->certificate,
                'selling_price' => $request->selling_price,
                'discount_price' => $request->discount_price,
                'prerequisites' => $request->prerequisites,
    
                'bestseller' => $request->bestseller,
                'featured' => $request->featured,
                'highestrated' => $request->highestrated,
                'status' => 1,
                'course_image' => $save_url,
                'created_at' => Carbon::now(),
    
            ]);


            /// Course Goals Add Form 

        $goles = Count($request->course_goals);
        if ($goles != NULL) {
            for ($i=0; $i < $goles; $i++) { 
                $gcount = new Course_goal();
                $gcount->course_id = $course_id;
                $gcount->goal_name = $request->course_goals[$i];
                $gcount->save();
            }
        }
        /// End Course Goals Add Form 

            
        } //end if

        $notification = array(
            'message' => 'Course Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.course')->with($notification); 

    } //End method


    public function EditCourse($id){

        $course = Course::find($id);
        $categories = Category::latest()->get();
        $subcategories = SubCategory::latest()->get();

        return view('instructor.courses.edit_course',compact('course', 'categories', 'subcategories'));

    }// End Method


}
