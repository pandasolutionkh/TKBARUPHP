@extends('layouts.adminlte.master')

@section('title')
    @lang('price.category.title')
@endsection

@section('page_title')
    <span class="fa fa-barcode fa-fw"></span>&nbsp;@lang('price.category.page_title')
@endsection

@section('page_title_desc')
    @lang('price.category.page_title_desc')
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render('price_level_today_price_update_bycat', $currentProductType->hId()) !!}
@endsection

@section('content')
    <div id="categoryPriceVue">
        <form id="categoryPriceForm" class="form-horizontal" method="post" @submit.prevent="validateBeforeSubmit()">
            {{ csrf_field() }}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('price.category.header.title', ['product_type' => $currentProductType->name])</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="form-group">
                            <label for="inputDate"
                                   class="col-sm-2 control-label">@lang('price.category.field.input_date')</label>
                            <div class="col-sm-4">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <vue-datetimepicker id="inputDate" name="inputed_date" value="" v-model="inputed_date" v-validate="'required'" format="DD-MM-YYYY hh:mm A" v-on:change="updateInputDate(inputed_date)"></vue-datetimepicker>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div v-bind:class="{ 'form-group': true, 'has-error':errors.has('inputed_market_price') }">
                            <label for="inputMarketPrice"
                                   class="col-sm-2 control-label">@lang('price.category.field.market_price')</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control text-right" name="inputed_market_price" v-model="market_price"
                                       v-validate="'required|numeric:2'" v-on:keyup="updateMarketPrice(market_price)" data-vv-as="{{ trans('price.stock.field.market_price') }}">
                            </div>
                            <span v-show="errors.has('inputed_market_price')" class="help-block" v-cloak>@{{ errors.first('inputed_market_price') }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('price.category.field.price')</label>
                            <div v-for="(p, pIdx) in price" v-bind:class="{ 'col-sm-2':true, 'has-error':errors.has('price_' + pIdx) }">
                                <input type="hidden" v-model="p.price_level_id" name="price_level_id[]">
                                <input type="text" class="form-control text-right" name="price[]"
                                       v-validate="'required|numeric:2'" v-model="p.price" aria-describedby="helpBlock"
                                       v-bind:data-vv-name="'price_' + pIdx" v-bind:data-vv-as="'{{ trans('price.stock.field.price') }} ' + (pIdx + 1)">
                                <span id="helpBlock" class="help-block" v-bind:title="getPriceLevelType(p.price_level_id) + ' ' + getPriceLevelValue(p.price_level_id)">
                                    @{{ getPriceLevelName(p.price_level_id) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">@lang('price.category.field.affected_stocks')</label>
                            <div class="col-sm-10">
                                <label class="control-label control-label-normal">
                                    <span class="control-label-normal">
                                        @foreach ($stocks as $s)
                                            {{ $s->product->name }}
                                            <br>
                                        @endforeach
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-toolbar">
                                <button id="submitButton" type="submit" name="submit"
                                        class="btn btn-primary pull-right">@lang('buttons.submit_button')</button>
                                <a id="cancelButton" class="btn btn-primary pull-right"
                                   href="{{ route('db.price.today') }}">@lang('buttons.cancel_button')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom_js')
    <script type="application/javascript">
        var app = new Vue({
            el: '#categoryPriceVue',
            data: {
                priceLevels: JSON.parse('{!! htmlspecialchars_decode($priceLevels) !!}'),
                inputed_date: '',
                market_price: '',
                price: []
            },
            mounted: function() {
                var vm = this;

                for (var i = 0; i < vm.priceLevels.length; i++) {
                    vm.price.push({
                        price_level_id: vm.priceLevels[i].id,
                        price: ''
                    });
                }
            },
            methods: {
                validateBeforeSubmit: function() {
                    var vm = this;
                    this.$validator.validateAll().then(function(isValid) {
                        if (!isValid) return;
                        $('#loader-container').fadeIn('fast');
                        axios.post('{{ route('api.post.db.price.category.update', $currentProductType->hId()) }}' + '?api_token=' + $('#secapi').val(), new FormData($('#categoryPriceForm')[0]))
                            .then(function(response) {
                                window.location.href = '{{ route('db.price.today') }}';
                            }).catch(function(e) {
                            $('#loader-container').fadeOut('fast');
                            if (e.response.data.errors != undefined && Object.keys(e.response.data.errors).length > 0) {
                                for (var key in e.response.data.errors) {
                                    for (var i = 0; i < e.response.data.errors[key].length; i++) {
                                        vm.$validator.errors.add('', e.response.data.errors[key][i], 'server', '__global__');
                                    }
                                }
                            } else if (e.response.data != undefined) {
                                for (var key in e.response.data) {
                                    vm.$validator.errors.add('', e.response.data[key][0], 'server', '__global__');
                                }
                            } else {
                                vm.$validator.errors.add('', e.response.status + ' ' + e.response.statusText, 'server', '__global__');
                                if (e.response.data.message != undefined) { console.log(e.response.data.message); }
                            }
                        });
                    });
                },
                updateInputDate: function(inputDate) {
                    var vm = this;
                    for (var i = 0; i < vm.price.length; i++) {
                        vm.price[i].input_date = inputDate;
                    }
                },
                updateMarketPrice: function(marketPrice) {
                    var vm = this;

                    for (var i = 0; i < vm.price.length; i++) {
                        var priceLevel = _.find(vm.priceLevels, { id: vm.price[i].price_level_id });
                        var price = 0;

                        if (priceLevel.type === 'PRICELEVELTYPE.INC') {
                            price = parseFloat(priceLevel.increment_value) + parseFloat(marketPrice);
                        } else {
                            price = parseFloat(priceLevel.percentage_value) * parseFloat(marketPrice) + parseFloat(marketPrice);
                        }

                        vm.price[i].price = isNaN(price ) ? '':price;
                        vm.price[i].market_price = marketPrice;
                    }
                },
                getPriceLevelType: function(priceLevelId) {
                    var vm = this;
                    var pl = _.find(vm.priceLevels, { id: priceLevelId });
                    return pl ? pl.i18nType:'';
                },
                getPriceLevelName: function(priceLevelId) {
                    var vm = this;
                    var pl = _.find(vm.priceLevels, { id: priceLevelId });
                    return pl ? pl.name:'';
                },
                getPriceLevelValue: function(priceLevelId) {
                    var vm = this;
                    var pl = _.find(vm.priceLevels, { id: priceLevelId });
                    return pl.type === 'PRICELEVELTYPE.INC' ? pl.increment_value:pl.percentage_value;
                }
            }
        });
    </script>
@endsection
