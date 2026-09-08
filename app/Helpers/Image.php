<?php

function imageUploadManager($image, $slug, $path)
{
    $path = 'uploads/'.$path.'/';
    $image_name = $path.$slug.time().uniqid().'.'.$image->getClientOriginalExtension();
    $path = public_path($path);
    if (! file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $image->move($path, $image_name);

    return $image_name;
}

function imageUpdateManager($image, $slug, $path, $old_image)
{
    $path = 'uploads/'.$path.'/';
    $image_name = $path.$slug.time().uniqid().'.'.$image->getClientOriginalExtension();
    $path = public_path($path);
    if (! file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $image->move($path, $image_name);

    imageDeleteManager($old_image);

    return $image_name;
}

function imageDeleteManager($old_image)
{
    if (file_exists($old_image) && $old_image != 'default.jpg') {
        @unlink($old_image);
    }
}

function imageShow($image)
{
    if ($image) {
        if (file_exists(public_path($image))) {
            return asset($image);
        } else {
            return asset('uploads/default.jpg');
        }
    } else {
        return asset('uploads/default.jpg');
    }
}

function imageExists($image)
{
    return file_exists(public_path($image));
}
