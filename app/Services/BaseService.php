<?php

namespace App\Services;


use App\Models\Admin\Media;
use Illuminate\Database\Eloquent\Model;

class BaseService
{
    private $model;
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function index($req, $with = [])
    {
        return $req->per_page == 'all' ? $this->getAll($req, $with) : $this->paginatePerPage($req, $with);
    }

    public function query($req, $with = [])
    {
        return $this->withSearch($req);
    }

    public function getAll($req, $with)
    {
        return $this->withSearch($req)->with($with)->get();
    }

    public function paginatePerPage($req, $with)
    {
        return $this->withSearch($req)->with($with)
            ->paginate($req->perpage ? $req->perpage : 6);
    }

    private function withSearch($req)
    {
        $mode = $this->model;
        $orderBy = $req->orderBy ? $req->orderBy : 'id';
        $seq = $req->seq == 'true' ? 'desc' : 'asc';
        // $searchQuery = trim(preg_replace('/[ ,$]+/', '', $req->query('searchText')));
        $searchQuery = trim($req->query('searchText'));
        $query = $mode::where(function ($q) use ($searchQuery, $mode) {
            $searchable = get_class($mode) == 'App\Models\Property' ? ['title', 'description', 'price'] : $mode->getFillable();
            foreach ($searchable as $col) {
                $q->orWhere($col, 'like', "%{$searchQuery}%");
            }
        })->orderBy($orderBy, $seq);
        // if (auth()->user() && auth()->user()->role != 'admin') {
        //     $query = $query->where('user_id', auth()->user()->id);
        // }
        return $query;
    }

    protected function sendGeneralEmail($data)
    {
        // try {
        //     Mail::to($data['email'])
        //         ->queue(new GeneralEmail($data));
        // } catch (Exception $e) {
        // }
    }

    public function handlePhoto($photo_id)
    {
        $photo = Media::find($photo_id);
        if ($photo) {
            $photo->update(['status' => 'using']);
        }
    }
}
