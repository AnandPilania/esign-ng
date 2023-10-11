@extends('admin::backend.admin.pdf.layouts')
@section('content')
@php
$request = request();
@endphp

<div id="assen">
    @if(isset($data['header']))
        <div class="assen__head">
            <h1>商品紹介</h1>
            <h2>{{ $data['client_name'] }}</h2>
        </div>
    @endif
    @php
        $i = $data['index'];
    @endphp
    @foreach($data['products'] as $item)
    <section class="assen__section">
        <div class="assen__title">
            <div>{{ $i++ }}</div>
            <h2>{{ $item['name_sale_product_classifications'] }}</h2>
        </div>
        <div class="assen__content">
            <div class="assen__info">
                <ul class="table-info">
                    <li>
                        <div class="table-info__th">メーカー名</div>
                        <div class="table-info__td">{{ $item['maker_name'] }}</div>
                    </li>
                    <li>
                        <div class="table-info__th">商品名</div>
                        <div class="table-info__td">{{ $item['name'] }}</div>
                    </li>
                    <li>
                        <div class="table-info__th">容量</div>
                        <div class="table-info__td">{{ $item['capacity'] }}</div>
                    </li>
                    <li>
                        <div class="table-info__th">商品コード</div>
                        <div class="table-info__td">{{ $item['code'] }}</div>
                    </li>
                </ul>
            </div>
            <div class="assen__image">
                <div class="p-15">
                    <img src="{{ \Core\Helpers\StorageHelper::assetProductImage( $item['image1'] ) }}" >
                </div>
            </div>
            <div class="assen__price">
                <div class="p-15">
                    <ul class="price">
                        <li>
                            <div class="price__item">
                                <div class="price__title">メーカー希望小売り価格</div>
                                <div class="price__content">{{ $item['market_price'] }}</div>
                            </div>
                        </li>
                        <li>
                            <div class="price__item">
                                <div class="price__title">価格</div>
                                <div class="price__content">{{ $item['estimation_price'] }}</div>
                            </div>
                        </li>
                        <li>
                            <div class="price__item">
                                <div class="price__title">点数</div>
                                <div class="price__content">{{ $item['score'] }}</div>
                            </div>
                        </li>
                    </ul>
                    <ul class="efficacy">
                        <li>
                            <div class="efficacy__title">効能</div>
                            <div class="efficacy__content">{{ $item['efficacy'] }}</div>
                        </li>
                        <li>
                            <div class="efficacy__title">商品サイズ</div>
                            <div class="efficacy__content">{{ $item['tate'] }} × {{ $item['yoko'] }} × {{ $item['taka'] }} mm</div>
                        </li>
                        <li>
                            <div class="efficacy__title">重量</div>
                            <div class="efficacy__content">{{ $item['weight'] }} {{ \Core\Helpers\Common::strWeight($item['weight_number']) }}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    @endforeach
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
    .p-15{
        padding: 15px;
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
        margin-top: 10px;
        background-color: #f6f0e6;
        width: 100%;
        float: left;
    }

    .assen__title{
        position: relative;
        text-align: center;
    }
    .assen__title div{
        background-color: #cbcbcb;
        border-left: 10px solid #c998c3;
        width: 13%;
        float: left;
        height: 48px;
        line-height: 48px;
    }
    .assen__title h2{
        background: #ebc7de;
        width: 33%;
        min-width: 200px;
        border-radius: 0 0 10px 0;
        padding: 15px 10px;
    }

    .table-info li{
        padding: 4.05mm 0;
        border-bottom: 1px solid #ebc7de;
    }
    .table-info__th{
        float: left;
        padding-left: 3mm;
        border-left: 1mm solid #c998c3;
        width: 30mm;
    }
    .table-info__td{
        float: right;
        text-align: right;
    }

    .assen__image img{
        margin: 15px;
    }

    .price{
        margin: 0 -5px;
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
        font-weight: bold;
    }

    .efficacy li{
        margin-top: 10px;
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
    }

    /*======Style for A4: Option 2======*/
    .p-15{
        padding-left: 5px;
        padding-right: 5px;
    }
    .assen__content{
        font-size: 10px;
        padding-top: 20px;
        padding-bottom: 20px;
    }
    .assen__info{
        float: left;
        width: 50mm;
        padding: 0 15px 15px;
    }
    .assen__image{
        float: left;
        width: 30mm;
    }
    .assen__image img{
        /*max-width: 175px;*/
        /*max-height: 200px;*/
        height: auto;
        width: auto;
        margin: auto;
    }
    .assen__price{
        float: left;
        width: 100mm;
    }
    .table-info__th{
        width: 15mm;
        padding-left: 3px;
    }
    .price{
        margin: 0 -3px;
    }
    .price__item{
        padding: 0 3px;
    }
    .price__title{
        font-size: 9px;
    }
    .efficacy__title{
        width: 15mm;
    }
    .efficacy li{
        margin-top: 7px;
    }
    /*=====End: Style A4: Option 2=====*/

</style>
@endsection
