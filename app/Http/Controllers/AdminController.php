<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard(){
        return view ('admin.index');
    }
    //end method



    public function AdminLogout(Request $request)
    {   
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login');
    } //end method



    public function AdminLogin (){

        return view('admin.admin_login');

    } //end method

    public function AdminProfile(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile_view',compact('profileData'));

    } //end method

    public function AdminProfileStore(Request $request){
        $id = Auth::user()->id;
        $data = User::find($id);
        $data->name = $request->name;
        $data->username = $request->username;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        if($request->file('photo')){
            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/'.$data->photo));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $data['photo'] = $filename;
        }

        $data->save();

        $notification = array(
            'message' => 'Admin Profile Updated Successfully ',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
        


    } //end method

    Public function AdminChangePassword(){

        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_change_password',compact('profileData'));


    } //end method


    public function AdminPasswordUpdate (Request $request){
        ///validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',

        ]);

        if(!Hash::check($request->old_password, auth::user()->password)){
            $notification = array(
                'message' => 'Old Password Does Not Match!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }

        // update the new password

        User::whereId(auth::user()->id)->update([
            'password'=> Hash::make($request->new_password)
        ]);

        $notification = array(
            'message' => 'Password Changed Successfully',
            'alert-type' => 'success'
        );
        return back()->with($notification);

    }  //end method


    public function BecomeInstructor(){


        return view('frontend.instructor.reg_instructor');


    }  // End Method


    public function InstructorRegister(Request $request){

        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','unique:users'],

        ]);

        User::insert([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' =>'instructor',
            'status' =>'0',


        ]);

        $notification = array(
            'message' => 'Instructor Registed Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('instructor.login')->with($notification);


    } //End Method


    public function AllInstructor(){

        $allinstructor = User::where('role','instructor')->latest()->get();
        return view('admin.backend.instructor.all_instructor',compact('allinstructor'));

    }// End Method

    public function UpdateUserStatus(Request $request){

        $userId = $request->input('user_id');
        $isChecked = $request->input('is_checked',0);

        $user = User::find($userId);
        if ($user){
            $user->status = $isChecked;
            $user->save();

        }

        return response()->json(['message' => 'User Status Updated Successfully']);

    } //End Method









}