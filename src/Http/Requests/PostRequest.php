<?php

namespace Canvas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->has('approved_at')) {
            $user = $this->user('canvas');
            if ($user === null) {
                return false;
            }

            $this->merge([
                'approved_by' => $this->get('approved_at') === null ? null : $user->id
            ]);
            return $user->role > 1;
        }
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
            'slug' => [
                'required',
                'alpha_dash',
                'max:100',
                Rule::unique('blog_posts')->where(function ($query) {
                    return $query->where('slug', request('slug'))->where('blogger_id', request()->user('canvas')->id)->where('uuid', '!=', request('uuid'));
                })->whereNull('deleted_at'),
            ],
            'title' => 'required|max:90',
            'summary' => 'nullable|string|max:144',
            'body' => 'nullable|string',
            'published_at' => 'nullable|date',
            'approved_at' => 'nullable|date',
            'approved_by' => 'nullable|integer',
            'featured_image' => 'nullable|string',
            'featured_image_caption' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
