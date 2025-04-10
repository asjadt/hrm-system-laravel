<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUploadRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'video' => 'required|mimes:mp4,mov,avi,wmv,webm,mkv,flv,m4v,3gp,ogg,qt,mpeg,mpg,asf,rm,swf,ogg,ogv,ogm,ogx,wmv,amv,m2v,m4p,mp2,mpe,m1v,m2ts,mxf,roq,rmvb,vob|max:25000',
        ];

    }
}
