<?php

namespace framework;
class Page
{
    //每页显示数量
    protected $number;
    //总共多少数据
    protected $totalCount;
    //总共多少页
    protected $pageCount;
    //当前页
    protected $page;
    //url
    protected $url;

    public function __construct($number, $totalCount)
    {
        $this->number = $number;
        $this->totalCount = $totalCount;
        //得到总页数
        $this->pageCount = $this->getPageCount();
        $this->page = $this->getPage();
        $this->url = $this->getUrl();
    }

    /**
     * @desc 得到总页数
     * @return float
     */
    protected function getPageCount()
    {
        return ceil($this->totalCount / $this->number);
    }

    /**
     * @desc 获得当前页
     * @return float|int|mixed
     */
    protected function getPage()
    {
        if (empty($_GET['page'])) {
            $page = 1;
        } elseif ($_GET['page'] > $this->pageCount) {
            $page = $this->pageCount;
        } elseif ($_GET['page'] < 1) {
            $page = 1;
        } else {
            $page = $_GET['page'];
        }
        return $page;
    }

    /**
     * @desc 得到处理好的url
     * @return string
     */
    protected function getUrl()
    {
        //得到协议
        $scheme = $_SERVER['REQUEST_SCHEME'];
        //得到主机名
        $host = $_SERVER['SERVER_NAME'];
        //得到端口号
        $port = $_SERVER['SERVER_PORT'];
        //得到uri
        $uri = $_SERVER['REQUEST_URI'];
        //将uri中的page=1删除
        $uriArray = parse_url($uri);
        $path = $uriArray['path'] ?: '';
        if (!empty($uriArray['query'])) {
            parse_str($uriArray['query'], $array);
            unset($array['page']);
            $query = http_build_query($array);
            if ($query) {
                $path .= '?' . $query;
            }
        }
        return $scheme . '://' . $host . ':' . $port . $path;
    }

    /**
     * @desc 拼接page
     * @param $str
     * @return string
     */
    protected function setUrl($str)
    {
        if (strstr($str, '?')) {
            $url = $this->url . '&' . $str;
        } else {
            $url = $this->url . '?' . $str;
        }
        return $url;
    }

    /**
     * @desc 得到全部url
     * @return array
     */
    public function allUrl()
    {
        return [
            'firstUrl' => $this->firstUrl(),
            'prevUrl' => $this->prevUrl(),
            'nextUrl' => $this->nextUrl(),
            'endUrl' => $this->endUrl()
        ];
    }

    /**
     * @desc 得到首页url
     * @return string
     */
    public function firstUrl()
    {
        return $this->setUrl('page=1');
    }

    /**
     * @desc 得到上一页url
     * @return string
     */
    public function prevUrl()
    {
        if ($this->page -1 < 1) {
            $page = 1;
        } else {
            $page = $this->page - 1;
        }
        return $this->setUrl('page=' . $page);
    }

    /**
     * @desc 得到下一页url
     * @return string
     */
    public function nextUrl()
    {
        if ($this->page + 1 > $this->pageCount) {
            $page = $this->pageCount;
        } else {
            $page = $this->page + 1;
        }
        return $this->setUrl('page=' . $page);
    }

    /**
     * @desc 得到尾页url
     * @return string
     */
    public function endUrl()
    {
        return $this->setUrl('page=' . $this->pageCount);
    }

    /**
     * @desc 从数据库读取数据
     * @return string
     */
    public function limit()
    {
        $offset = ($this->page - 1) * $this->number;
        return $offset . ',' . $this->number;
    }
}