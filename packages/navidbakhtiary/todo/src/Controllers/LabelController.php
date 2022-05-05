<?php

namespace NavidBakhtiary\ToDo\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use NavidBakhtiary\ToDo\Models\Label;
use NavidBakhtiary\ToDo\Responses\BadRequestResponse;
use NavidBakhtiary\ToDo\Responses\CreatedResponse;
use NavidBakhtiary\ToDo\Responses\UnprocessableEntityResponse;

class LabelController extends Controller
{
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|unique:labels,name',
        ]);
        if ($validation->fails()) 
        {
            return BadRequestResponse::sendErrors($validation->errors()->messages());
        }

        if($label = Label::create($request->all()))
        {
            return CreatedResponse::sendLabel($label);
        }
        return UnprocessableEntityResponse::sendMessage();
    }
}
