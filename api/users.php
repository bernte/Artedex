<?php

$app->get('/users', function () use ($app) {
    // query database for all users
    $users = R::find('users');

    // send response header for JSON content type
    $app->response()->header('Content-Type', 'application/json');

    // return JSON-encoded response body with query results
    echo json_encode(R::exportAll($users));
});

$app->get('/users/:id', function ($id) use ($app) {
    try {
        // query database for single user
        $user = R::findOne('users', 'id=?', array($id));

        if ($user) {
            // if found, return JSON response
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($user));
        } else {
            // else throw exception
            throw new ResourceNotFoundException();
        }
    } catch (ResourceNotFoundException $e) {
        // return 404 server error
        $app->response()->status(404);
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

$app->post('/debug/login', function () use ($app) {
    echo 'Login route';
});