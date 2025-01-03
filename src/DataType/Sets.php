<?php

namespace OpenAdminCore\Admin\RedisManager\DataType;

use Illuminate\Support\Arr;

class Sets extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $key)
    {
        return $this->getConnection()->smembers($key);
    }

    /**
     * {@inheritdoc}
     */
    /*
    public function update(array $params)
    {
        $key = Arr::get($params, 'key');

        if (Arr::has($params, 'member')) {
            $member = Arr::get($params, 'member');
            $this->getConnection()->sadd($key, $member);
        }

        if (Arr::has($params, '_editable')) {
            $new = Arr::get($params, 'value');
            $old = Arr::get($params, 'pk');

            $this->getConnection()->transaction(function ($tx) use ($key, $old, $new) {
                $tx->srem($key, $old);
                $tx->sadd($key, $new);
            });
        }
    }
    */

    /**
     * {@inheritdoc}
     */
    public function store(array $params)
    {
        $key = Arr::get($params, 'key');
        $ttl = Arr::get($params, 'ttl');
        $values = Arr::get($params, 'value');

        $this->getConnection()->sadd($key, $values);

        if ($ttl > 0) {
            $this->getConnection()->expire($key, $ttl);
        }

        return redirect(route('redis-edit-key', [
            'conn' => request('conn'),
            'key'  => $key,
        ]));
    }

    /**
     * Remove a member from a set.
     *
     * @param array $params
     *
     * @return int
     */
    public function remove(array $params)
    {
        $key = Arr::get($params, 'key');
        $member = Arr::get($params, 'member');

        return $this->getConnection()->srem($key, $member);
    }

    public function form()
    {
        $this->form->hidden('conn')->value($this->conn);
        $this->form->hidden('type')->value('set');
        $this->form->text('key');
        $this->form->number('ttl')->default(-1);
        $this->form->list('value');
    }
}
