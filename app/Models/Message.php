<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Message extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'messages';
    public $timestamps = false;
    protected $fillable = [
        'message_thread_id',
        'from_id',
        'admin_id',
        'message_content',
        'file_name',
        'file_path',
        'file_type',
        'read_at',
        'created_at'
    ];



    public function countMessageUnreadUser($user_id, $thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->where('from_id', '!=', $user_id)
            ->where('read_at', null)
            ->count('from_id');
        return $result;
    }

    public function countMessageUnreadForAdmin($thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->whereNotNull('from_id')
            ->where('read_at', null)
            ->count('from_id');
        return $result;
    }

    public function countMessageUnreadAdmin($thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->where('admin_id', '!=', null)
            ->where('read_at', null)
            ->count('admin_id');
        return $result;
    }

    public function getLastMessageUser($user_id, $thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->where('from_id', '!=', $user_id)
            ->orderBy('created_at', 'DESC')
            ->first();
        return $result;
    }

    public function getLastMessageForAdmin($thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->whereNotNull('from_id')
            ->orderBy('created_at', 'DESC')
            ->first();
        return $result;
    }

    public function getLastMessageAdmin($thread_id)
    {
        $result = $this->where('message_thread_id', $thread_id)
            ->where('admin_id', '!=', null)
            ->orderBy('created_at', 'DESC')
            ->first();
        return $result;
    }

    public function getAllMessageByThread($thread_id,$conditions){
        $query = $this->where('message_thread_id', $thread_id);
        if (isset($conditions->keyword)) {
            $query->where('message_content', 'like', '%' . $conditions->keyword . '%');
        }

        $result = $query->orderBy('created_at', 'ASC')->get();
        if (count($result) > 0) {
            foreach ($result as $item) {
                if ($item['file_path']) {
                    $item['file_path'] = config('app.app_resource_path') . $item['file_path'];
                }
            }
        }
        return $result;
    }

 
    public function readMarkUser($thead_id, $user_id)
    {
        $this->where('message_thread_id', $thead_id)
            ->where('from_id', '!=', $user_id)
            ->orWhereNotNull('admin_id')
            ->where('read_at', null)
            ->update(['read_at' => Carbon::now()]);
    }

    public function readMarkAdminIndex($thead_id){
        $this->where('message_thread_id', $thead_id)
            ->whereNotNull('from_id')
            ->where('read_at', null)
            ->update(['read_at' => Carbon::now()]);
    }
}
