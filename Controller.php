<?php

class Example
{
    public function copy(Test $test)
    {
        $copied_test = new Test();

        $copied_test->name = $test->name." КОПИЯ";
        $copied_test->description = $test->description;
        $copied_test->solving_time = $test->solving_time;
        $copied_test->previous_test = $test->previous_test;
        $copied_test->next_test = $test->next_test;
        $copied_test->user_id = $test->user_id;

        $copied_test->save();

        $copied_test->categories()->sync( array_column($test->categories->toArray(), 'id') );

        $test->roles()->sync( array_column($test->roles->toArray(), 'id') );

        foreach ($test->questions as $question)
        {
            $copied_question = new Question();

            $copied_question->index = $question->id;
            $copied_question->solving_time = $question->solving_time;
            $copied_question->test_id = $copied_test->id;
            $copied_question->body = $question->body;
            $copied_question->options = $question->options;
            $copied_question->note = $question->note;

            $copied_question->save();
        }

        foreach ($test->groups as $group)
        {
            $copied_group = new Group();

            $copied_group->index = $group->index;
            $copied_group->solving_time = $group->solving_time;
            $copied_group->test_id = $copied_test->id;
            $copied_group->name = $group->name;
            $copied_group->options = $group->options;
            $copied_group->note = $group->note;

            $copied_group->save();

            foreach ($group->questions as $group_question)
            {
                $copied_group_question = new Question();

                $copied_group_question->index = $group_question->index;
                $copied_group_question->solving_time = $group_question->solving_time;
                $copied_group_question->test_id = $copied_test->id;
                $copied_group_question->group_id = $copied_group->id;
                $copied_group_question->body = $question->body;
                $copied_group_question->options = $question->options;
                $copied_group_question->note = $question->note;

                $copied_group_question->save();
            }
        }

        return redirect()->route('route.name', $test->id);
    }

    public function destroy(Group $group)
    {
        try
        {
            $group->delete();
        }
        catch (Exception $e)
        {
            return redirect()->back()->with('failed', 'Что-то пошло не так');
        }

        return redirect()->back()->with('success', 'Группа успешно удалена');
    }

    public function search(Request $request) {

        $request->validate([ 'q' => 'required' ]);

        $query = $request->q;

        $postHeading = Post::where('heading', 'LIKE', '%'.$query.'%')->get();

        $postBody = Post::where('body', 'LIKE', '%'.$query.'%')->get();

        $lessonTheme = Lesson::where('theme', 'LIKE', '%'.$query.'%')->get();

        $lessonBody = Lesson::where('body', 'LIKE', '%'.$query.'%')->get();

        $posts = $postHeading->merge($postBody);
        $lessons = $lessonTheme->merge($lessonBody);
        $result = $posts->merge($lessons);

        return view('req', compact('result'), ['req' => $query]);
    }
}