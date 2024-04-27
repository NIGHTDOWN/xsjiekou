<?php

/**统计缺失的章节，并且去掉重复章节 */




require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;

class fixlistnovel extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "修复章节排序"; //书籍来源描述
    public  $_domian = "https://www.lanovel.club"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "c1774fb28759d14916a641f04df67bca";
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";
    public $appneedinfo = [
        "version" => "1.3.5",
        "language" => "MS",
    ];

    //一些临时数据，无需变动
    private $upinfo = [];
    private $upcount = 0;
    private $tokens = [];
    private $rmbookid = [];
    public $errors = [];
    private $last = 0;
    private $lastbid;
    private $loop = [];
    public function start($bookid)
    {

        $data = $this->getbooklist($bookid);

        $this->logend($this->upcount, $this->upinfo, sizeof($this->rmbookid));
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($id)
    {

        $data = T($this->dbbook)->set_field($this->db_id . ',update_status,lang')
            ->set_where([$this->db_id => $id])
            ->get_all();
        foreach ($data  as $book) {
            $this->_booklang = $book['lang'];
            $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
            $this->loaddb($this->booktype, $this->booklang);
            $w = [$this->db_id      => $book[$this->db_id],  "status"       => 1, 'isdelete' => 0];
            if ($this->booktype == 1) {
                $filed = 'section_id';
            } else {
                $filed = 'cart_section_id';
            }

            $seclist = T($this->dbsec)->set_field('list_order,' . $filed)->set_where($w)->order_by('list_order asc')->get_all();
            foreach ($seclist as $key => $value) {
                # code...
                //相等的不操作
                $norder = $key + 1;
                $oorder = $value['list_order'];
                if ($norder != $oorder) {
                    T($this->dbsec)->update(['list_order' => $key + 1], [$filed => $value[$filed]]);
                }
            }
        }
        return sizeof($data);
    }
    // 获取远程小说详情，根据实际情况修改fun

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $this->setdomain($this->_domian);
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
        $this->logstart(__FILE__);
    }
    //调试类
    public function help()
    {
        d('接受参数--bookid=id，修复指定id书籍排序');
    }
    public function in()
    {
        $bookids = $this->getargv(['bookid::']);

        $bookid = $bookids['bookid'];
        if ($bookid) {
            $this->start($bookid);
        }
    }
}
$ob = new fixlistnovel();
// $ob->reg();

// $ob->start(1039);
$ob->in();
