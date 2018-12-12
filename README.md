### TlePage For Typecho

TlePage是一个可以为文章分页（含AJAX分页）的Typecho插件

程序有可能会遇到bug不改版本号直接修改代码的时候，所以扫描以下二维码关注公众号“同乐儿”，可直接与作者二呆产生联系，不再为bug烦恼，随时随地解决问题。

<img src="http://me.tongleer.com/content/uploadfile/201706/008b1497454448.png">

#### 使用方法：
第一步：下载本插件，放在 `usr/plugins/` 目录中（插件文件夹名必须为TlePage）；<br />
第二步：激活插件；<br />
第三步：填写配置；<br />
第四步：配置参数；<br />
第五步：将以下代码放到主题目录下post.php中输出内容的位置进行替换（如：parseContent($this)或$this->content()）；<br />
&lt;?php TlePage_Plugin::parseContent($this); ?><br />
第六步：在编写的文章中间通过点击编辑器摘要按钮，插入HR分割线（----------），即为分页分割线；<br />
第七步：完成。

#### 使用注意：
此插件使用php5.6编写，php7.0“可能”会报语法错误，建议使用php5.6，因为7.0实在太高了=_=!

#### 与我联系：
作者：二呆<br />
微信：Diamond0422<br />
网站：http://www.tongleer.com/<br />
Github：https://github.com/muzishanshi/TlePage

#### 更新记录：
2018-12-12增加了对于如何在标题添加第几页方法的提示及ajax的提示信息<br />
2018-08-30对Markdown内容及图片格式的显示做了优化<br />
2018-08-20第一版本问世