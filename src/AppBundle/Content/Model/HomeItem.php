<?php

namespace AppBundle\Content\Model;

class HomeItem
{
    private $videoIcon;
    private $link;
    private $image;

    public function __construct(bool $videoIcon, string $link, string $image)
    {
        $this->videoIcon = $videoIcon;
        $this->link = $link;
        $this->image = $image;
    }

    public function hasVideoIcon(): bool
    {
        return $this->videoIcon;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getImage(): string
    {
        return $this->image;
    }
}
