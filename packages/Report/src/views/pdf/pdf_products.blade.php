@extends('admin::backend.admin.pdf.layouts')
@section('content')
@php
$request = request();
@endphp

<div id="assen">
    <div class="assen__head">
        <h1>商品紹介</h1>
        <h2>外傷殺菌・外用薬類</h2>
    </div>
    <section class="assen__section">
        <div class="assen__title">
            <h2>商品名</h2>
            <div>マキロンS</div>
        </div>
        <div class="assen__content">
            <ul class="table-info">
                <li>
                    <div class="table-info__th">容量</div>
                    <div class="table-info__td">40ml</div>
                </li>
                <li>
                    <div class="table-info__th">メーカー名</div>
                    <div class="table-info__td">第一三共</div>
                </li>
                <li>
                    <div class="table-info__th">商品コード</div>
                    <div class="table-info__td">00000000</div>
                </li>
            </ul>
            <ul class="price">
                <li>
                    <div class="price__item">
                        <div class="price__title">メーカー希望小売り価格</div>
                        <div class="price__content">500</div>
                    </div>
                </li>
                <li>
                    <div class="price__item">
                        <div class="price__title">価格</div>
                        <div class="price__content">20000</div>
                    </div>
                </li>
                <li>
                    <div class="price__item">
                        <div class="price__title">点数</div>
                        <div class="price__content">1</div>
                    </div>
                </li>
            </ul>
            <div class="thumbnail">
                <img src="https://shop.sukoyaka.life/shop/media/catalog/product/cache/1/small_image/184x214/9df78eab33525d08d6e5fb8d27136e95/9/5/9505-1_30.jpg" >
                <img src="https://shop.sukoyaka.life/shop/media/catalog/product/cache/1/small_image/184x214/9df78eab33525d08d6e5fb8d27136e95/9/5/9505-1_30.jpg" >
            </div>
            <div class="introduce">
                <h2 class="introduce__head">商品紹介</h2>
                <div class="introduce__content">
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                    テキストテキストテキストテキストテキストテキストテキストテキストテキストテキス
                </div>
            </div>
            <ul class="efficacy">
                <li>
                    <div class="efficacy__wrap">
                        <div class="efficacy__title">服用</div>
                        <div class="efficacy__content">頓用</div>
                    </div>
                </li>
                <li>
                    <div class="efficacy__wrap">
                        <div class="efficacy__title">商品サイズ</div>
                        <div class="efficacy__content">85 × 30 × 95 mm</div>
                    </div>
                </li>
                <li>
                    <div class="efficacy__wrap">
                        <div class="efficacy__title">対象年齢</div>
                        <div class="efficacy__content">&nbsp;</div>
                    </div>
                </li>
                <li>
                    <div class="efficacy__wrap">
                        <div class="efficacy__title">重量</div>
                        <div class="efficacy__content">40 g</div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</div>
@stop

@section('addCss')

    {{--Style for size A4--}}
<style>
    body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
        margin: 0;
        padding: 0;
    }

    fieldset, img {
        border: 0;
        vertical-align: middle;
        max-width: 100%
    }

    code, em, strong, th {
        font-style: normal;
        font-weight: normal;
    }

    h1, h2, h3, h4, h5, h6 {
        font-size: 100%;
        font-weight: normal;
    }
    *,*::before,*::before{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        list-style: none;
        outline: none;
    }
    ul{
        list-style: none;
    }
    /*共有*/
    #assen{
        font-size: 14px;
        line-height: 1.3;
    }

    .assen__head h1{
        margin-bottom: 2rem;
        padding: 5px 0;
        border-radius: 100px;
        text-align: center;
        line-height: 1.5;
        font-size: 24px;
        font-weight: 600;
        box-shadow: 0 1px #ccc;
        background: linear-gradient(to bottom,  rgba(246,240,230,1) 0%,rgba(255,255,255,1) 50%,rgba(246,240,230,1) 100%);
    }
    .assen__head h2{
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #ccc;
        font-size: 20px;
        line-height: 1.3;
        margin-bottom: 15px
    }

    .assen__section{
        background-color: #f6f0e6;
    }

    .assen__title{
        position: relative;
        text-align: center;
    }
    .assen__title h2{
        background: #ebc7de;
        width: 33%;
        min-width: 200px;
        border-radius: 0 0 10px 0;
        padding: 15px 10px;
        border-left: 10px solid #c998c3;
        float: left;
        font-size: 18px;
    }
    .assen__title div{
        height: 48px;
        line-height: 43px;
        text-align: left;
        border-top: 5px solid #ebc7de;
        float: left;
        padding-left: 15px;
        font-size: 18px;
    }

    .assen__content{
        padding: 30px 15px;
    }

    .table-info{
        margin-bottom: 15px;
    }
    .table-info li{
        padding: 2mm 2mm 2mm 0;
        border-bottom: 1px solid #ebc7de;
        border-top: 1px solid #ebc7de;
        float: left;
        width: 32.23%;
    }
    .table-info__th{
        float: left;
        padding-left: 2mm;
        border-left: 2px solid #ebc7de;
        width: 30mm;
        padding-top: 2mm;
        padding-bottom: 2mm;
    }
    .table-info__td{
        float: right;
        text-align: right;
        padding-top: 2mm;
        padding-bottom: 2mm;
    }
    .table-info li:first-child .table-info__th{
        border-left: 0;
    }

    .price{
        margin: 0 -5px 15px;
    }
    .price li{
        width: 33.333333%;
        float: left;
    }
    .price__item{
        padding: 0 5px;
    }
    .price__title{
        background: #ebc7de;
        padding: 8px 5px;
        text-align: center;
    }
    .price__content{
        background: #fff;
        text-align: right;
        padding: 7px 5px;
    }

    .thumbnail{
        margin-bottom: 15px;
        text-align: center;
        background: #fff;
        padding: 10px;
        word-spacing: 10px;
    }
    .thumbnail img{
        width: 100px;
        display: inline-block;
        border: 1px solid #ebc7de;
    }

    .introduce{
        background: #fff;
        margin-bottom: 10px;
    }
    .introduce__head{
        background: #ebc7de;
        text-align: center;
        padding: 8px 5px;
    }
    .introduce__content{
        padding: 15px;
    }

    .efficacy{
        margin: -5px;
    }
    .efficacy li{
        float: left;
        width: 48.5%;
        padding: 5px;
    }
    .efficacy__wrap{
        background: #ebc7de;
    }
    .efficacy__title{
        padding: 8px 5px;
        float: left;
        width: 25mm;
    }
    .efficacy__content{
        padding: 8px 5px;
        float: right;
        text-align: right;
        background: #fff;
        min-height: 30px;
    }
</style>
@endsection
