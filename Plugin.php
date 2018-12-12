<?php
/**
 * TlePage是一个可以为文章分页（含AJAX分页）的插件
 * @package TlePage For Typecho
 * @author 二呆
 * @version 1.0.3
 * @link http://www.tongleer.com/
 * @date 2018-12-12
 */

class TlePage_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
        return _t('插件已经激活');
    }

    // 禁用插件
    public static function deactivate(){
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		//版本检查
		$version=file_get_contents('http://api.tongleer.com/interface/TlePage.php?action=update&version=3');
		$div=new Typecho_Widget_Helper_Layout();
		$div->html('
			<small>
				版本检查：'.$version.'
				<h3>使用方法</h3>
				<span><p>第一步（可选）：配置下方参数；</p></span>
				<span>
					第二步：将以下代码放到主题目录下post.php中输出内容的位置进行替换（如：parseContent($this)或$this->content()）；
					<pre><font color="red">&lt;?php TlePage_Plugin::parseContent($this); ?></font></pre>
				</span>
				<span><p>第三步：在编写的文章中间通过点击编辑器分割线&lt;hr>按钮，插入HR分割线（----------），即为分页分割线；</p></span>
				<h3>注意事项</h3>
				<span>
					1、如果文章中含有图片，需要将typecho默认图片添加方式修改为<font color="red">&lt;img src"" /></font>形式的html代码，如有所不便，敬请谅解。<br />
					2、如果在禁用ajax分页情况下，想要让标题加上第几页的后缀，可以自行在主题目录下header.php的&lt;title>中加上<font color="red">&lt;?php if(@$_GET["page_now"]>1){echo " - 第".@$_GET["page_now"]."页";}?></font>代码即可。
				</span>
			</small>
		');
		$div->render();
		
		$isAjaxPage = new Typecho_Widget_Helper_Form_Element_Radio('isAjaxPage', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'n', _t('是否启用AJAX分页'), _t("启用后文章页会使用AJAX无刷新技术进行分页，否则会在每一页之间进行跳转。但对于主题不包含jquery的情况，需要手动添加<font color='red'>&lt;script src='https://libs.baidu.com/jquery/1.11.1/jquery.min.js'>&lt;/script></font>代码才可以，如有不便，敬请谅解。"));
        $form->addInput($isAjaxPage->addRule('enum', _t(''), array('y', 'n')));
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('TlePage');
    }
	
	/**
     * 输出
     *
     * @access public
     * @return void
     */
    public static function parseContent($obj){
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		$option=self::getConfig();
		$db = Typecho_Db::get();
		$query= $db->select()->from('table.contents')->where('cid = ?', $obj->cid); 
		$row = $db->fetchRow($query);
		$log_content=$row["text"];
		
		$Tle_Page_Mark = "----------";
		if(strpos($log_content, $Tle_Page_Mark)) $Y = "Y";
		if(isset($Y)){
			$Tle_content_list = explode($Tle_Page_Mark, $log_content);
			$Tle_page_count = count($Tle_content_list);
			$page_now = !empty($_GET['page_now']) ? intval($_GET['page_now']) : 1;
			$page_now = ($page_now > $Tle_page_count && $Tle_page_count>0) ? $Tle_page_count : $page_now;
			$log_content = stripslashes($Tle_content_list[$page_now -1]);
			if($option->isAjaxPage == 'y'){
				$Tle_log_content = '
					<div id="pagearea"><!--这里添加分页内容--></div>
					<div id="pageBar"><!--这里添加分页按钮栏--></div>
					<input type="hidden" id="articlecid" value="'.$obj->cid.'" />
					<script>
					var curPage;/*当前页数*/
					var totalItem;/*总记录数*/
					var pageSize;/*每一页记录数*/
					var totalPage;/*总页数*/
					 
					//获取分页数据
					function turnPage(page){
					  $.ajax({
						type: "POST",
						url: "'.$plug_url.'/TlePage/ajax/paging_article.php",/*这里是请求的后台地址，自己定义*/
						data: {"page_now":page,"cid":$("#articlecid").val()},
						dataType: "json",
						beforeSend: function() {
						  $("#pagearea").append("加载中...");
						},
						success: function(json) {
						  $("#pagearea").empty();/*移除原来的分页数据*/
						  totalItem = json.totalItem;
						  pageSize = json.pageSize;
						  curPage = page;
						  totalPage = json.totalPage;
						  var data_content = json.log_content;
						  $("#pagearea").append(data_content);
						},
						complete: function() {    /*添加分页按钮栏*/
						  getPageBar();
						},
						error: function() {
						  alert("数据加载失败");
						}
					  });
					}
					/*获取分页条（分页按钮栏的规则和样式根据自己的需要来设置）*/
					function getPageBar(){
					  if(curPage > totalPage) {
						curPage = totalPage;
					  }
					  if(curPage < 1) {
						curPage = 1;
					  }
					 
					  pageBar = "<div style=\"text-align:center;\">";
					 
					  /*如果不是第一页*/
					  if(curPage != 1){
						pageBar += "<a href=\"javascript:turnPage(1)\">首页</a>&nbsp;";
						pageBar += "<a href=\"javascript:turnPage("+(curPage-1)+")\">上一页</a>&nbsp;";
					  }
					 
					  /*显示的页码按钮(5个)*/
					  var start,end;
					  if(totalPage <= 5) {
						start = 1;
						end = totalPage;
					  } else {
						if(curPage-2 <= 0) {
							start = 1;
							end = 5;
						} else {
							if(totalPage-curPage < 2) {
								start = totalPage - 4;
								end = totalPage;
							} else {
								start = curPage - 2;
								end = curPage + 2;
							}
						}
					  }
					  
					  for(var i=start;i<=end;i++) {
						if(i == curPage) {
							pageBar += "<a href=\"javascript:turnPage("+i+")\">"+i+"</a>&nbsp;";
						} else {
							pageBar += "<a href=\"javascript:turnPage("+i+")\">"+i+"</a>&nbsp;";
						}
					  }
					  
					  /*如果不是最后页*/
					  if(curPage != totalPage){
						pageBar += "<a href=\"javascript:turnPage("+(parseInt(curPage)+1)+")\">下一页</a>&nbsp;";
						pageBar += "<a href=\"javascript:turnPage("+totalPage+")\">尾页</a>";
					  }
						pageBar += "</div>"; 
					  $("#pageBar").html(pageBar);
					}
					 
					/*页面加载时初始化分页*/
					$(function() {
					  turnPage(1);
					});
					</script>
				';
				echo $Tle_log_content;
			}else{
				$content=$log_content;
				
				$i=0;
				$match_1 = "/(\!\[).*?\]\[(\d)\]/";
				preg_match_all ($match_1,$content,$matches_1,PREG_PATTERN_ORDER);
				if(count($matches_1)>0&&count($matches_1[0])>0){
					foreach($matches_1[0] as $val_1){
						$content=str_replace($val_1,"",$content);
						$img_prefix=substr($val_1,strlen($val_1)- 3,3);
						$img_prefix=str_replace("[","\[",$img_prefix);
						$img_prefix=str_replace("]","\]",$img_prefix);
						$match_2 = "/(".$img_prefix.":).*?((.gif)|(.jpg)|(.bmp)|(.png)|(.GIF)|(.JPG)|(.PNG)|(.BMP))/";
						preg_match_all ($match_2,$content,$matches_2,PREG_PATTERN_ORDER);
						if(count($matches_2)>0&&count($matches_2[0])>0){
							foreach($matches_2[0] as $val_2){
								$img=substr($val_2,4);
								$content=preg_replace($match_2,'<img src="'.$img.'" />',$content);
								break;
							}
						}else{
							break;
						}
						$i++;
					}
				}
				
				if($page_now==1&&strpos($content, '<!--markdown-->')===0){
					$content=substr($content,15);
				}
				$content=Markdown::convert($content);
				$content = str_replace("<img ", "<img width=\"100%\"", $content);
				echo $content;
				if($page_now>$Tle_page_count){
					$page_now=$Tle_page_count;
				}
				if($page_now<=1){
					$before_page=1;
					if($Tle_page_count>1){
						$after_page=$page_now+1;
					}else{
						$after_page=1;
					}
				}else{
					$before_page=$page_now-1;
					if($page_now<$Tle_page_count){
						$after_page=$page_now+1;
					}else{
						$after_page=$Tle_page_count;
					}
				}
				?>
				<div style="text-align:center;">
				  <?php if($page_now!=1){?>
					<a href="<?=$obj->permalink;?>?page_now=1">首页</a>&nbsp;
				  <?php }?>
				  <?php if($page_now>1){?>
					<a href="<?=$obj->permalink;?>?page_now=<?=$before_page;?>">上一页</a>&nbsp;
				  <?php }?>
				  <?php if($page_now<$Tle_page_count){?>
					<a href="<?=$obj->permalink;?>?page_now=<?=$after_page;?>">下一页</a>&nbsp;
				  <?php }?>
				  <?php if($page_now!=$Tle_page_count){?>
					<a href="<?=$obj->permalink;?>?page_now=<?=$Tle_page_count;?>">尾页</a>
				  <?php }?>
				</div>
				<?php
			}
		}else{
			echo $obj->content;
		}
	}
}