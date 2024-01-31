# 说明文档

## 模板

### 试卷模板

模板支持word和html，以行为单位进行解析，对于word来说，一个回车，视为一行，对于html来说，一对`p`标签，视为一行

#### 模板标签

##### 题型

顶格书写，当解析器遇到当前行为如下文字时，认为是`题型`:

* `单项选择题`
* `多项选择题`
* `判断题`
* `问答题`

> 题型可以省略，当题型省略时，解析器认为当前题目与上一题目是相同的题型

##### 题干

顶格书写，当解析器遇到`*`开头时，认为是`题干`，题干可以为多行，也可以包括图片，题干的文字为多行时，仅能在第一行添加`*`

##### 选项

顶格书写，当解析器遇到`A.`(大写字母+半角句点)开头时，认为是选择题的选项，选项可以为多行，也可以包括图片，选项的文字为多行时，仅能在第一行添加`A.`
> 仅选择题适用
##### 答案

顶格书写，当解析器遇到`答案:`(汉字答案+英文冒号)开头时，认为是答案，答案为一行时，写在冒号之后，答案为多行或包括图片时，仅能在第一行添加`答案:`

##### 解析

顶格书写，当解析器遇到`解析:`(汉字解析+英文冒号)开头时，认为是解析，解析为一行时，写在冒号之后，解析为多行或包括图片时，仅能在第一行添加`解析:`

## 安装

```
composer install jerryaicn/word
```

## 使用

### 将word转换为html

```
$path = "./example.doxs";
$word = new \Jerryaicn\Word($path);
$word->getContentAsHtml()
```

### 从word中解析试题

```
$path = "./example.doxs";
$wordParser = new WordParser($path);
$wordParser->setDebug(true);
$examParser = new ExamParser();
$examParser->setDebug(true);
$raw = $wordParser->getContentAsHtml();
$result = $examParser->parseFromHtml($raw);
```

### 从html中解析试题

```
$path = "./example.doxs";
$wordParser = new WordParser($path);
$wordParser->setDebug(true);
$examParser = new ExamParser();
$examParser->setDebug(true);
$raw = $wordParser->getContentAsHtml();
$result = $examParser->parseFromHtml($raw);
```

## 例子

examples 目录包括完整的示例，使用方法

```
cd examples
php -S 0.0.0.0:8000
```

用浏览器打开 `http://localhost:8000/exam.html`

