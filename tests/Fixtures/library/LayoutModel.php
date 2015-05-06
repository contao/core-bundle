<?php

namespace Contao\Fixtures;

use Contao\Model;

class LayoutModel extends Model
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function row()
    {
        return $this->data;
    }

    public function setRow(array $data)
    {
        $this->data = $data;
    }
}
