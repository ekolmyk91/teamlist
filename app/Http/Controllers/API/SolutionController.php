<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Solution;
use Image;

class SolutionController extends Controller
{

    public function index()
    {
        //        $solutions = Solution::all();

        //        return response()->json($solutions);

        $solutions = [
          [
            'id'             => 1,
            'title'          => "Test",
            'author'         => 'Testname Testsurname',
            'content'        => "<p>test</p>\r\n<p>formated</p>\r\n<p>text</p>",
            'archive'        => null,
            'active'         => 1,
            'likes'          => '325',
            'views'          => '627',
            'comments_count' => 15,
            'comments'       => [
              [
                'author'     => "Lorem Ipsum",
                'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum velit diam, ullamcorper consectetur vestibulum nec, finibus a nibh. In varius.',
                'created_at' => "2019-11-27 07:19:37",
              ],
            ],
            'created_at'     => "2019-11-27 07:19:37",
            'updated_at'     => "2019-11-27 07:19:37",
          ],
          [
            'id'             => 2,
            'title'          => "Lorem Ipsum",
            'author'         => 'Admin Testsurname',
            'content'        => "<p>test</p>\r\n<p>formated</p>\r\n<p>text</p>",
            'archive'        => null,
            'active'         => 1,
            'likes'          => '325',
            'views'          => '627',
            'comments_count' => 15,
            'comments'       => [
              [
                'author'     => "Lorem Ipsum",
                'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum velit diam, ullamcorper consectetur vestibulum nec, finibus a nibh. In varius.',
                'created_at' => "2019-11-27 07:19:37",
              ],
            ],

            'created_at' => "2019-11-27 07:19:37",
            'updated_at' => "2019-11-27 07:19:37",
          ],
          [
            'id'             => 3,
            'title'          => "Why do we use it?",
            'author'         => 'Testname Testsurname',
            'content'        => "<p>test</p>\r\n<p>formated</p>\r\n<p>text</p>",
            'archive'        => null,
            'active'         => 1,
            'likes'          => '325',
            'views'          => '627',
            'comments_count' => 15,
            'comments'       => [],
            'created_at'     => "2019-11-27 07:19:37",
            'updated_at'     => "2019-11-27 07:19:37",
          ],
          [
            'id'             => 4,
            'title'          => "Where can I get some?",
            'author'         => 'Testname Testsurname',
            'content'        => "<p>test</p>\r\n<p>formated</p>\r\n<p>text</p>",
            'archive'        => null,
            'active'         => 1,
            'likes'          => '325',
            'views'          => '627',
            'comments_count' => '15',
            'created_at'     => "2019-11-27 07:19:37",
            'updated_at'     => "2019-11-27 07:19:37",
          ],
          [
            'id'             => 5,
            'title'          => "Where does it come from?",
            'author'         => 'Loren Testsurname',
            'content'        => "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum velit diam, ullamcorper consectetur vestibulum nec, finibus a nibh. In varius. </p>",
            'archive'        => null,
            'active'         => 1,
            'likes'          => '325',
            'views'          => '627',
            'comments_count' => '15',
            'created_at'     => "2019-11-27 07:19:37",
            'updated_at'     => "2019-11-27 07:19:37",
          ],
        ];

        return response()->json($solutions);
    }

    public function show($id)
    {
        //        $solution = Solution::find($id);
        //
        //        return response()->json($solution);
        $solution = [
          'id'             => 2,
          'title'          => "Lorem Ipsum",
          'author'         => 'Admin Testsurname',
          'content'        => "<p>test</p>\r\n<p>formated</p>\r\n<p>text</p>",
          'archive'        => null,
          'active'         => 1,
          'likes'          => '325',
          'views'          => '627',
          'comments_count' => 15,
          'comments'       => [
            [
              'author'     => "Lorem Ipsum",
              'content'    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum velit diam, ullamcorper consectetur vestibulum nec, finibus a nibh. In varius.',
              'created_at' => "2019-11-27 07:19:37",
            ],
          ],

          'created_at' => "2019-11-27 07:19:37",
          'updated_at' => "2019-11-27 07:19:37",
        ];

        return response()->json($solution);
    }
}
