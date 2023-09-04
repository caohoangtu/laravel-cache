<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class Todo extends Model
{
    use HasFactory;

    const CACHE_GET_ALL = 'todo_get_all';
    const DETAIL_TODO = 'detail_todo';

    protected $table="todo";

    /**
     * @param array $attributes
     * @return Todo
     */
    public function createTodo(array $attributes){
        $todo = new self();
        $todo->title = $attributes["title"];
        $todo->content = $attributes["content"];
        $todo->save();

        Cache::forget(self::CACHE_GET_ALL);

        return $todo;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getTodo(int $id){
        $todo = Cache::get(self::DETAIL_TODO.$id);

        if(!empty($todo)){
            return $todo;
        }

        $todo = $this->where("id",$id)->first();

        Cache::add(self::DETAIL_TODO.$id,$todo);

        return $todo;
    }

    /**
     * @return Todo[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getsTodo(){

        $todos = Cache::get(self::CACHE_GET_ALL);
        if(!empty($todos)){
            return $todos;
        }
        //get todos in database
        $todos = $this::all();

        Cache::add(self::CACHE_GET_ALL,$todos);

        return $todos;
    }

    /**
     * @param int $id
     * @param array $attributes
     * @return mixed
     */
    public function updateTodo(int $id, array $attributes){
        $todo = $this->getTodo($id);
        if($todo == null){
            throw new ModelNotFoundException("Cant find todo");
        }
        $todo->title = $attributes["title"];
        $todo->content = $attributes["content"];
        $todo->save();

        Cache::forget(self::DETAIL_TODO.$id);

        return $todo;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteTodo(int $id){
        $todo = $this->getTodo($id);
        if($todo == null){
            throw new ModelNotFoundException("Todo item not found");
        }
        Cache::forget(self::DETAIL_TODO.$id);
        return $todo->delete();
    }
}
