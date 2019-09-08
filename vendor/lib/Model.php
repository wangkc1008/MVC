<?php

namespace framework;
class model
{
    protected $host;
    protected $userName;
    protected $password;
    protected $dbName;
    protected $charset;
    protected $tablePrefix;

    protected $link;
    protected $tableName;

    protected $sql;
    protected $options;

    public function __construct($config)
    {
        $this->host = $config['DB_HOST'];
        $this->userName = $config['DB_USER'];
        $this->password = $config['DB_PWD'];
        $this->dbName = $config['DB_DBNAME'];
        $this->charset = $config['DB_CHARSET'];
        $this->tablePrefix = $config['DB_PREFIX'];
        //连接数据库
        $this->link = $this->connect();
        //得到表名
        $this->tableName = $this->getTableName();
        //初始化操作数组
        $this->initOptions();
    }

    protected function connect()
    {
        $link = mysqli_connect($this->host, $this->userName, $this->password);
        if (!$link) {
            exit('数据库连接失败');
        }
        mysqli_select_db($link, $this->dbName);
        mysqli_set_charset($link, $this->charset);
        return $link;
    }

    protected function getTableName()
    {
        if (!empty($this->tableName)) {
            return $this->tablePrefix . $this->tableName;
        }
        $tableName = strtolower(substr(get_class($this), 0, -5));
        return $tableName;
    }

    protected function initOptions()
    {
        $arr = ['table', 'where', 'field', 'limit', 'order', 'group', 'having'];
        foreach ($arr as $item) {
            $this->options[$item] = '';
            if ($item == 'table') {
                $this->options[$item] = $this->tableName;
            }
            if ($item == 'field') {
                $this->options[$item] = '*';
            }
        }
    }

    public function table($table)
    {
        if (empty($table)) {
            return $this;
        }
        if (is_string($table)) {
            $this->options['table'] = $table;
        }
        return $this;
    }

    public function field($field)
    {
        if (empty($field)) {
            return $this;
        }
        if (is_string($field)) {
            $this->options['field'] = $field;
        }
        if (is_array($field)) {
            $this->options['field'] = join(',', $field);
        }
        return $this;
    }

    public function where($where)
    {
        if (empty($where)) {
            return $this;
        }
        if (is_string($where)) {
            $this->options['where'] = 'WHERE ' . $where;
        }
        if (is_array($where)) {
            $where = $this->parseValue($where);
            $field = $this->parseWhere($where, ' AND ');
            $this->options['where'] = 'WHERE ' . $field;
        }
        return $this;
    }

    public function order($order)
    {
        if (empty($order)) {
            return $this;
        }
        if (is_string($order)) {
            $this->options['order'] = 'ORDER BY ' . $order;
        }
        return $this;
    }

    public function group($group)
    {
        if (empty($group)) {
            return $this;
        }
        if (is_string($group)) {
            $this->options['group'] = 'GROUP BY ' . $group;
        }
        return $this;
    }

    public function having($having)
    {
        if (empty($having)) {
            return $this;
        }
        if (is_string($having)) {
            $this->options['having'] = 'HAVING ' . $having;
        }
        return $this;
    }

    public function limit($limit)
    {
        if (empty($limit)) {
            return $this;
        }
        if (is_string($limit)) {
            $this->options['limit'] = 'LIMIT ' . $limit;
        }
        if (is_array($limit) && count($limit) <= 2) {
            $this->options['limit'] = 'LIMIT ' . join(',', $limit);
        }
        return $this;
    }

    public function select() {
        $sql = 'SELECT %FIELD% FROM %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT% ';
        $sql = str_replace(
            ['%FIELD%', '%TABLE%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
            [$this->options['field'], $this->options['table'], $this->options['where'], $this->options['group'], $this->options['having'], $this->options['order'], $this->options['limit']],
            $sql
        );
        $this->sql = $sql;
        return $this->query($sql);
    }

    public function insert($data)
    {
        $data = $this->parseValue($data);
        $field = join(',', array_keys($data));
        $value = join(',', array_values($data));
        $sql = "INSERT INTO %TABLE%(%FIELD%)VALUES(%VALUES%)";
        $sql = str_replace(
            ['%TABLE%', '%FIELD%', '%VALUES%'],
            [$this->options['table'], $field, $value],
            $sql
        );
        $this->sql = $sql;
        return $this->exec($sql, true);
    }

    public function update($data)
    {
        $data = $this->parseValue($data);
        $field = $this->parseWhere($data);

        $sql = "UPDATE %TABLE% SET %FIELD% %WHERE%";
        $sql = str_replace(
            ['%TABLE%', '%FIELD%', '%WHERE%'],
            [$this->options['table'], $field, $this->options['where']],
            $sql
        );
        $this->sql = $sql;
        return $this->exec($sql);
    }

    public function delete()
    {
        $sql = "DELETE FROM %TABLE% %WHERE%";
        $sql = str_replace(
            ['%TABLE%', '%WHERE%'],
            [$this->options['table'], $this->options['where']],
            $sql
        );
        $this->sql = $sql;
        return $this->exec($sql);
    }

    protected function parseValue($data)
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
            }
            $newData[$key] = $value;
        }
        return $newData;
    }

    protected function parseWhere($data, $str=',')
    {
        $newData = [];
        foreach ($data as $key => $value) {
            $newData[] = $key . '=' . $value;
        }
        $newData = join($str, $newData);
        return $newData;
    }

    public function __get($name)
    {
        if ($name == 'sql') {
            return $this->sql;
        }
        return false;
    }

    protected function query($sql)
    {
        $this->initOptions();
        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link)) {
            $newData = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $newData[] = $row;
            }
            return $newData;
        }
        return false;
    }

    protected function exec($sql, $isInsert = false)
    {
        $this->initOptions();
        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link)) {
            if ($isInsert) {
                return mysqli_insert_id($this->link);
            }
            return mysqli_affected_rows($this->link);
        }
        return false;
    }

    public function max($field)
    {
        $result = $this->field('max(' . $field . ') as max')->select();
        return $result ? $result[0]['max'] : false;
    }

    public function __destruct()
    {
        mysqli_close($this->link);
    }

    public function __call($name, $args)
    {
        if ('getBy' == substr($name, 0, 5)) {
            $key = strtolower(substr($name, 5));
            $result = $this->where([$key => $args[0]])->select();
            return $result;
        }
        return false;
    }

}