<template>
    <div>
        <div id="api">
            <el-form ref="form" :model="form" :rules="rules" method="post" label-width="120px" label-position="top"
                     autocomplete="off">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <h3>Откуда</h3>
                        <el-form-item label="Страна вылета" prop="countrySrc">
                            <el-input class="countrySrc"
                                      name="countrySrc"
                                      @blur="countrySrcBlur"
                                      @focus="focus('countrySrcHidden', 'countrySrc', '', '')"
                                      v-model="form.countrySrc">
                            </el-input>
                            <el-input type="hidden" class="countrySrcHidden"
                                      name="countrySrcHidden" v-model="form.countrySrcHidden">
                            </el-input>
                        </el-form-item>
                        <el-form-item
                            v-if="form.countrySrcHidden"
                            label="Город вылета"
                            prop="citySrc"
                        >
                            <el-input class="citySrc"
                                      name="citySrc"
                                      @blur="citySrcBlur"
                                      @focus="focus('citySrc', 'citySrc', 'Найди свой город вылета', '')"
                                      v-model="form.citySrc"
                            >
                            </el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <h3>Куда</h3>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-form-item label="Часть света">
                                <el-select v-model="form.continentDst"
                                           class="continentDst flights__select"
                                           value="random"
                                           @change="continentDstChange"
                                           name="continentDst">
                                    <el-option v-for="(value, key) in continents"
                                               :label="value"
                                               :value="key">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-form-item v-if="form.continentDst!=='random'"
                                      label="Страна"
                        >
                            <el-input class="countryDst"
                                      name="countryDst"
                                      @blur="countryDstBlur"
                                      @focus="focus('countryDstHidden', 'countryDst', 'любая', '')"
                                      v-model="form.countryDst">
                            </el-input>
                            <el-input type="hidden" class="countryDstHidden"
                                      name="countryDstHidden" v-model="form.countryDstHidden">
                            </el-input>
                        </el-form-item>
                        <el-form-item
                            v-if="form.continentDst!=='random' && form.countryDstHidden && form.countryDstHidden!=='любая'"
                            class="cityDstWrap"
                            label="Город"
                        >
                            <el-input class="cityDst"
                                      name="cityDst"
                                      @blur="cityDstBlur"
                                      @focus="focus('cityDstHidden', 'cityDst', 'любой', '')"
                                      v-model="form.cityDst">
                            </el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <h3>Интервал</h3>
                        <el-form-item label="Получать дешевые авиабилеты один раз в:">
                            <el-select v-model="form.interval"
                                       @change="intervalChange()"
                                       class="interval flights__select"
                                       value="60"
                                       name="interval">
                                <el-option label="Час" value="60"></el-option>
                                <el-option label="Четыре часа" value="240"></el-option>
                                <el-option label="Восемь часов" value="480"></el-option>
                                <el-option label="24 часа" value="1440"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="Получать информацию за крайние N, часов">
                            <el-select v-model="form.updated"
                                       class="interval flights__select"
                                       value="60"
                                       name="updated">
                                <el-option
                                    v-for="obj in updatedArrBuffer"
                                    :label="obj.label"
                                    :value="obj.value"
                                ></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <h3>Количество</h3>
                        <el-form-item label="Сколько дешевых авиабилетов показывать в сутки?">
                            <el-select v-model="form.limit" class="interval flights__select"
                                       name="interval" :value="0">
                                <el-option v-for="(val, index) in limitsValArr"
                                           :label="val" :value="index"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <h3>Полеты</h3>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-form-item label="">
                                <el-checkbox checked label="Туда"
                                             class="forward" name="forward"
                                             v-model="form.forward">
                                </el-checkbox>
                                <el-checkbox label="Туда-обратно"
                                             class="dual" name="dual"
                                             v-model="form.dual">
                                </el-checkbox>
                            </el-form-item>
                        </el-col>
                    </el-col>
                </el-row>
                <el-row>
                    <h3>Когда</h3>
                    <el-col v-for="index in datesCnt" :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
                        <div :class="'dateSelectWrap'+index">
                            <el-form-item :label="'Год вылета ('+index+')'">
                                <el-select :class="'flights__select yearSrc'+index"
                                           @change="selectYear(index)"
                                           value="random"
                                           name="yearSrc"
                                           v-model="form.date.year[index]">
                                    <el-option label="любой" value="random"></el-option>
                                    <el-option v-for="i in 31" :label="getFullYear - 1 + i"
                                               :value="getFullYear - 1 + i">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item :label="'Время года ('+index+')'">
                                <el-select :class="'flights__select seasonSrc'+index"
                                           name="seasonSrc"
                                           v-model="form.date.season[index]">
                                    <el-option v-for="(transcription, key) in seasonsTranscription"
                                               :label="transcription"
                                               :value="key">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item :label="'Месяц вылета ('+index+')'">
                                <el-select :class="'flights__select monthSrc'+index" name="monthSrc"
                                           v-model="form.date.month[index]">
                                    <el-option v-for="(month, key) in months[index]"
                                               :label="month"
                                               :value="key">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </el-col>
                </el-row>
                <el-row type="flex" justify="end">
                    <el-button
                        v-if="datesCnt > 1"
                        @click="subDate" class="subDateSelection"
                        type="danger" icon="el-icon-minus" circle></el-button>
                    <el-button
                        v-if="datesCnt < 3"
                        @click="addDate" class="addDateSelection"
                        type="primary" icon="el-icon-plus" circle></el-button>
                </el-row>
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <el-form-item label="Язык">
                            <el-select v-model="form.language"
                                       class="language flights__select"
                                       value="ru"
                                       name="language">
                                <el-option v-for="(value, key) in languages"
                                           :label="value"
                                           :value="key">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <el-form-item label="Валюта">
                            <el-select v-model="form.currency"
                                       class="currency flights__select"
                                       value="RUB"
                                       @change="setCurrencyForUrl()"
                                       name="currency">
                                <el-option v-for="(value, key) in currencies"
                                           :label="value"
                                           :value="key">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <el-form-item label="Валюта метапоисковика">
                            <el-select v-model="form.currencyForUrl"
                                       class="currencyForUrl flights__select"
                                       value="RUB"
                                       name="currencyForUrl">
                                <el-option v-for="(value, key) in currencies"
                                           :label="value"
                                           :value="key">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <el-form-item label="Пассажиры">
                            <el-select v-model="form.passengers"
                                       class="passengers flights__select"
                                       value="url"
                                       name="passengers">
                                <el-option v-for="(value, key) in passengersArr"
                                           :label="value"
                                           :value="key">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="12">
                        <h3>Стоимость, {{shortCurrencyTranslateFunc(form.currencyForUrl.toLowerCase())}}</h3>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-form-item label="">
                                <el-row>
                                    <el-checkbox
                                        v-model="form.randomCost" label="Любая"
                                        class="randomCost" name="randomCost"
                                        @change="randomCostClick">
                                    </el-checkbox>
                                </el-row>
                                <el-row>
                                    <label>Выберите стоимость</label>
                                    <el-row type="flex" justify="center">
                                        <el-col :xs="22" :sm="6" :md="6" :lg="6" :xl="6" style="width: 94%;">
                                            <div class="cost" id="sliderCost">
                                            </div>
                                        </el-col>
                                    </el-row>
                                    <el-row :gutter="20">
                                        <el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
                                            <el-input-number v-model="form.costMin"
                                                             class="costText flights__number_input"
                                                             id="costTextMin"
                                                             :min="costMinConstant"
                                                             :max="costMaxConstant" :step="100"
                                            >
                                            </el-input-number>
                                        </el-col>
                                        <el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
                                            <el-input-number v-model="form.costMax"
                                                             class="costText flights__number_input"
                                                             id="costTextMax"
                                                             :min="costMinConstant"
                                                             :max="costMaxConstant" :step="100"
                                            >
                                            </el-input-number>
                                        </el-col>
                                    </el-row>
                                </el-row>
                            </el-form-item>
                        </el-col>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-link
                            @click="optional=!optional"
                            id="optionalCheckbox"
                            type="primary">
                            Дополнительные параметры
                        </el-link>
                    </el-col>
                </el-row>
                <el-row v-if="optional">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <el-form-item label="Показывать авиабилеты только с выгодой больше, %">
                            <el-select v-model="form.optional.profitability"
                                       class="profitability flights__select" name="profitability"
                                       placeholder="please select">
                                <el-option label="30" value="30" selected></el-option>
                                <el-option label="40" value="40"></el-option>
                                <el-option label="50" value="50"></el-option>
                                <el-option label="60" value="60"></el-option>
                                <el-option label="70" value="70"></el-option>
                                <el-option label="80" value="80"></el-option>
                                <el-option label="90" value="90"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row v-if="optional">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <h3>Вылеты в определенные дни недели</h3>
                        <el-form-item label="">
                            <el-checkbox
                                :disabled="allDaysDisable"
                                v-model="checkAllDaysVar"
                                @change="setAllDays"
                                class="dayOfWeek dayOfWeekAll"
                                name="dayOfWeekAll">
                                Все дни недели
                            </el-checkbox>
                            <br>
                            <el-link
                                @click.prevent="allDaysClick()" class="daysLink"
                                type="primary">
                                Уточнить
                            </el-link>
                            <div v-if="daysShow" class="days">
                                <el-checkbox
                                    @change="checkAllDays"
                                    v-for="(dayOfWeek, index) in daysOfWeek"
                                    v-model="form.optional.days[index]"
                                    :class="'dayOfWeek dayOfWeek' + (index+1)"
                                    :name="'dayOfWeek' + (index+1)">
                                    {{dayOfWeek}}
                                </el-checkbox>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row v-if="optional">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-link
                            @click.prevent="showAddionalWeather=!showAddionalWeather" class="weatherLink"
                            type="primary">
                            Погода
                        </el-link>
                        <br>
                        <div v-if="showAddionalWeather" class="weather">
                            <p class="optional">Температура воздуха днем в день прилета</p>
                            <br/>
                            <el-checkbox
                                v-model="sliderDayTempLabelChecked"
                                @change="clickDayTempRandom"
                                class="sliderDayTempLabel"
                                name="sliderDayTempLabel">
                                Любая
                            </el-checkbox>
                            <el-row type="flex" justify="center">
                                <el-col style="width: 94%;">
                                    <div id="sliderDayTemp"></div>
                                </el-col>
                            </el-row>
                            <br>
                            <div class="temperatureDayText"></div>
                        </div>
                        <br>
                        <div v-if="showAddionalWeather" class="weather">
                            <p>Температура воздуха ночью в день прилета</p>
                            <br/>
                            <el-checkbox
                                v-model="sliderNightTempLabelChecked"
                                @change="clickNightTempRandom"
                                class="sliderNightTempLabel"
                                name="sliderNightTempLabel">
                                Любая
                            </el-checkbox>
                            <el-row type="flex" justify="center">
                                <el-col style="width: 94%;">
                                    <div id="sliderNightTemp"></div>
                                </el-col>
                            </el-row>
                            <br>
                            <div class="temperatureNightText"></div>
                        </div>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="4">
                        <el-button type="primary" class="api__button" @click.prevent="modalShow">
                            Подписаться
                        </el-button>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8">
                        <el-link :underline="false" class="api__button" href="https://www.booking.com?aid=1540284"
                                 id="modifyLink" target="_blank">
                            Выбрать отель на booking.com
                        </el-link>
                        <el-row type="flex" justify="end">
                            <el-link href="https://join.booking.com/a/1539998" target="_blank">
                                <el-col>
                                    Зарегистрировать свой объект
                                </el-col>
                            </el-link>
                        </el-row>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <Modal :optional="optional" :display-modal="displayModal" :form="form"
               :seasons-transcription="seasonsTranscription" :dates-cnt="datesCnt"
               :show-addional-weather="showAddionalWeather" :days-of-week="daysOfWeek"
               :was-created="wasCreated" :id="id" :limits-val-arr="limitsValArr"/>
    </div>
</template>
<script>
    import Modal from "./Modal";
    import {store} from "../../store";
    import {mapState, mapActions} from 'vuex';
    import {getCityAviasalesInfo, getCountryCode, shortCurrencyTranslate} from "../../helpers";

    export default {
        props: {
            formProp: {
                type: Object,
                default: null
            }
        },
        data() {
            let validateCitySrc = (rule, value, callback) => {
                if (value === 'Найди свой город вылета') {
                    callback(new Error('Заполните поле'));
                } else {
                    callback();
                }
            };
            return {
                rules: {
                    countrySrc: [
                        {required: true, message: 'Заполните поле', trigger: 'hfghfgh'}
                    ],
                    citySrc: [
                        {required: true, message: 'Заполните поле', trigger: 'fghfg'},
                        {validator: validateCitySrc, trigger: 'fghfgh'}
                    ],
                },
                id: null,
                loading: false,
                continents: window.constants.CONTINENTS,
                limitsValArr: {
                    '0': 'все',
                    '20': 20,
                    '50': 50,
                    '100': 100,
                    '300': 300
                },
                updatedArrBuffer: [
                    {label: '1 час', value: 60},
                    {label: '2 часа', value: 60 * 2},
                    {label: '3 часа', value: 60 * 3},
                    {label: '4 часа', value: 60 * 4},
                    {label: '8 часов', value: 60 * 8},
                    {label: '12 часов', value: 60 * 12},
                    {label: '24 часа', value: 60 * 24},
                ],
                updatedArr: [
                    {label: '1 час', value: 60},
                    {label: '2 часа', value: 60 * 2},
                    {label: '3 часа', value: 60 * 3},
                    {label: '4 часа', value: 60 * 4},
                    {label: '8 часов', value: 60 * 8},
                    {label: '12 часов', value: 60 * 12},
                    {label: '24 часа', value: 60 * 24},
                ],
                form: {
                    language: 'ru',
                    currency: 'RUB',
                    currencyForUrl: 'RUB',
                    passengers: 'url',
                    countrySrc: '',
                    countrySrcHidden: '',
                    citySrc: 'Найди свой город вылета',
                    citySrcHidden: '',
                    cityDst: 'любой',
                    cityDstHidden: 'любой',
                    continentDst: 'random',
                    countryDst: 'любая',
                    countryDstHidden: 'любая',

                    interval: '60',
                    updated: 60,

                    limit: '0',

                    forward: true,
                    dual: true,

                    randomCost: true,
                    costMin: window.constants.COST_MIN,
                    costMax: window.constants.COST_MAX,

                    date: {
                        year: {1: 'random', 2: 'random', 3: 'random'},
                        season: {1: 'all', 2: 'all', 3: 'all'},
                        month: {1: '0', 2: '0', 3: '0'}
                    },

                    optional: {
                        profitability: '30',
                        days: [true, true, true, true, true, true, true],
                        dayTemp: {
                            min: 0,
                            max: 0
                        },
                        nightTemp: {
                            min: 0,
                            max: 0
                        },
                    }
                },
                initForm: this.initForm,
                showAddionalWeather: false,
                datesCnt: 1,
                optional: true,
                daysShow: false,
                daysOfWeek: window.constants.DAYS_OF_WEEK,
                allDaysDisable: true,
                seasonMonthsInterval: {
                    'all': {from: '01', to: '12'},
                    'winter': {from: 12, to: '02'}, 'spring': {from: '03', to: '05'},
                    'summer': {from: '06', to: '08'}, 'fall': {from: '09', to: 11}
                },
                sliderDayTempLabelChecked: true,
                sliderNightTempLabelChecked: true,
                seasonsTranscription: window.constants.SEASONS_TRANSCRIPTION,
                monthTranscription: window.constants.MONTHS_TRANSCRIPTION,
                seasonsMonths: {
                    'all': window.constants.MONTHS_TRANSCRIPTION,
                    'winter': {0: 'любой', 12: 'декабрь', 1: 'январь', 2: 'февраль'},
                    'spring': {0: 'любой', 3: 'март', 4: 'апрель', 5: 'май'},
                    'summer': {0: 'любой', 6: 'июнь', 7: 'июль', 8: 'август'},
                    'fall': {0: 'любой', 9: 'сентябрь', 10: 'октябрь', 11: 'ноябрь'},
                },
                months: {},
                checkAllDaysVar: true,
                displayModal: false,
                filter: {},
                sliderCost: null,
                wasCreated: false,
                citiesArray: []
            }
        },
        components: {
            Modal
        },
        created() {
            this.initForm = Object.assign({}, this.form);
            this.months = {1: this.monthTranscription, 2: this.monthTranscription, 3: this.monthTranscription};
        },
        async mounted() {
            this.showAddionalWeather = true;
            this.sliderCost = document.getElementById('sliderCost');
            let startCostMin = window.constants.COST_MIN;
            let startCostMax = window.constants.COST_MAX;
            if (this.formProp) {
                startCostMin = this.formProp.costMin;
                startCostMax = this.formProp.costMax;
            }
            noUiSlider.create(this.sliderCost, {
                start: [startCostMin, startCostMax],
                connect: true,
                step: 100,
                tooltips: [wNumb({decimals: 0}), wNumb({decimals: 0})],
                range: {
                    'min': window.constants.COST_MIN,
                    'max': window.constants.COST_MAX
                }
            });
            this.sliderCost.noUiSlider.on('update', (values, handle) => {
                this.form.costMin = parseInt(values[0]);
                this.form.costMax = parseInt(values[1]);
                if (this.form.randomCost &&
                    (this.form.costMin != window.constants.COST_MIN || this.form.costMax != window.constants.COST_MAX)) {
                    this.form.randomCost = false;
                }
            });

            this.makeCountryList(
                'https://api.cheapflights.sale/api/Places/countries',
                '.countrySrc .el-input__inner',
                'countrySrc',
                'countrySrcHidden',
                this.getCities,
                '.citySrc .el-input__inner',
                'citySrc',
                'citySrcHidden',
                false
            );
            this.$root.$on('sendRequestAcception', (data) => {
                this.sendRequestAcception(data.outputArr);
            });
            this.$root.$on('closeModal', (data) => {
                this.displayModal = false;
            });
            this.detectCurrentDate();
            if (this.formProp) {
                await this.fillFormFields();
            } else {
                if (this.user.country.id !== 0) {
                    this.form.countrySrc = this.user.country.title;
                    let countrySrcHidden = await getCountryCode(this.form.countrySrc);
                    this.setCountrySrcAndMakeCitySrcAutocomplete(
                        this.form.countrySrc,
                        countrySrcHidden,
                        'countrySrc',
                        'countrySrcHidden',
                        '.citySrc .el-input__inner',
                        'citySrc',
                        'citySrcHidden',
                        this.getCities,
                        false
                    );
                }
                if (this.user.city.id !== 0) {
                    this.form.citySrc = this.user.city.title;
                    this.form.citySrcHidden = this.user.city.title;
                }
            }
            this.intervalChange();
            store.dispatch('main/setLoadingFlag', false);
        },
        destroyed() {
            this.$root.$off('sendRequestAcception');
            this.$root.$off('closeModal');
        },
        computed: {
            ...mapState({
                currencies: (state) => state.main.currencies,
                passengersArr: (state) => state.main.passengers,
                languages: (state) => state.main.languages,
                user: (state) => state.main.user,
            }),

            getFullYear() {
                return new Date().getFullYear();
            },
            formCostMin() {
                return this.form.costMin;
            },
            formCostMax() {
                return this.form.costMax;
            },
            formSeason1() {
                return this.form.date.season[1];
            },
            formSeason2() {
                return this.form.date.season[2];
            },
            formSeason3() {
                return this.form.date.season[3];
            },
            formCountrySrc() {
                return this.form.countrySrc;
            },
            formCountryDst() {
                return this.form.countryDst;
            },
            formContinentDst() {
                return this.form.continentDst;
            },
            costMinConstant() {
                return window.constants.COST_MIN
            },
            costMaxConstant() {
                return window.constants.COST_MAX
            },
        },
        watch: {
            formContinentDst() {
                setTimeout(() => {
                    this.makeCountryList(
                        'https://api.cheapflights.sale/api/Countries/byContinent/' + this.form.continentDst,
                        '.countryDst .el-input__inner',
                        'countryDst',
                        'countryDstHidden',
                        this.getCities,
                    );
                }, 500);
            },
            formCostMin() {
                this.sliderCost.noUiSlider.set([(this.form.costMin), null]);
            },
            formCostMax() {
                this.sliderCost.noUiSlider.set([null, (this.form.costMax)]);
            },
            formSeason1() {
                return this.seasonSelect(this.form.date.season[1], 1);
            },
            formSeason2() {
                return this.seasonSelect(this.form.date.season[2], 2);
            },
            formSeason3() {
                return this.seasonSelect(this.form.date.season[3], 3);
            },
            formCountrySrc() {
                if (!this.form.countrySrc) {
                    this.form.countrySrcHidden = '';
                    this.form.citySrc = '';
                    this.form.citySrcHidden = '';
                }
            },
            formCountryDst() {
                if (!this.form.countryDst) {
                    this.form.countryDstHidden = 'любая';
                    this.form.cityDst = 'любой';
                    this.form.cityDstHidden = 'любой';
                }
            },
            showAddionalWeather() {
                if (this.showAddionalWeather) {
                    setTimeout(() => {
                        let sliderDayTemp = document.getElementById('sliderDayTemp');
                        let sliderNightTemp = document.getElementById('sliderNightTemp');

                        noUiSlider.create(sliderDayTemp, {
                            start: [
                                this.formProp && this.formProp.optional ? this.formProp.optional.dayTemp.min : -50,
                                this.formProp && this.formProp.optional ? this.formProp.optional.dayTemp.max : 50
                            ],
                            connect: true,
                            step: 1,
                            tooltips: [wNumb({decimals: 0}), wNumb({decimals: 0})],
                            range: {
                                'min': -50,
                                'max': 50
                            }
                        });
                        noUiSlider.create(sliderNightTemp, {
                            start: [
                                this.formProp && this.formProp.optional ? this.formProp.optional.nightTemp.min : -50,
                                this.formProp && this.formProp.optional ? this.formProp.optional.nightTemp.max : 50
                            ],
                            connect: true,
                            step: 1,
                            tooltips: [wNumb({decimals: 0}), wNumb({decimals: 0})],
                            range: {
                                'min': -50,
                                'max': 50
                            }
                        });
                        let sliderDayTempLabel = document.querySelector('.sliderDayTempLabel');
                        sliderDayTemp.noUiSlider.on('update', (values, handle) => {
                            this.form.optional.dayTemp = {
                                min: values[0],
                                max: values[1],
                            };
                            if (this.sliderDayTempLabelChecked && (values[0] != -50 || values[1] != 50)) {
                                this.sliderDayTempLabelChecked = false;
                            }
                        });
                        let sliderNightTempLabel = document.querySelector('.sliderNightTempLabel');
                        sliderNightTemp.noUiSlider.on('update', (values, handle) => {
                            this.form.optional.nightTemp = {
                                min: values[0],
                                max: values[1],
                            };
                            if (this.sliderNightTempLabelChecked && (values[0] != -50 || values[1] != 50)) {
                                this.sliderNightTempLabelChecked = false;
                            }
                        });
                    }, 500);
                }
            }
        },
        methods: {
            shortCurrencyTranslateFunc(currency) {
                return shortCurrencyTranslate(currency);
            },
            detectSeason(month) {
                switch (month) {
                    case 12:
                    case 1:
                    case 2:
                        return 'winter';
                    case 3:
                    case 4:
                    case 5:
                        return 'spring';
                    case 6:
                    case 7:
                    case 8:
                        return 'summer';
                    case 9:
                    case 10:
                    case 11:
                        return 'autumn';
                }
            },
            setCountrySrcAndMakeCitySrcAutocomplete(label, value, country, countryHidden, citySelector, city, cityHidden, callback = null, flag = true) {
                this.form[country] = label;
                this.form[countryHidden] = value;
                if (callback) {
                    callback(citySelector, city, cityHidden, countryHidden, flag);
                }
            },
            selectYear(index) {
                this.form.date.season[index] = 'all';
                this.form.date.month[index] = '0';
                let d = new Date();
                let month = d.getMonth() + 1;
                let year = d.getFullYear();
                if (this.form.date.year[index] <= year) {
                    let season = '';
                    switch (month) {
                        case 12:
                        case 1:
                        case 2:
                            this.seasonsTranscription = {
                                'all': 'любое',
                                'winter': 'зима',
                                'spring': 'весна',
                                'summer': 'лето',
                                'fall': 'осень',
                            };
                            break;
                        case 3:
                        case 4:
                        case 5:
                            this.seasonsTranscription = {
                                'all': 'любое',
                                'spring': 'весна',
                                'summer': 'лето',
                                'fall': 'осень',
                            };
                            break;
                        case 6:
                        case 7:
                        case 8:
                            this.seasonsTranscription = {'all': 'любое', 'summer': 'лето', 'fall': 'осень',};
                            break;
                        case 9:
                        case 10:
                        case 11:
                            this.seasonsTranscription = {'all': 'любое', 'fall': 'осень',};
                            break;
                    }
                    this.setMonth(this.monthTranscription, index);
                } else {
                    this.seasonsTranscription = {
                        'all': 'любое',
                        'winter': 'зима',
                        'spring': 'весна',
                        'summer': 'лето',
                        'fall': 'осень',
                    };
                    this.months[index] = this.monthTranscription;
                }
            },
            setMonth(months, index) {
                let d = new Date();
                let month = d.getMonth() + 1;
                let year = d.getFullYear();
                if (this.form.date.year[index] <= year) {
                    this.months[index] = {
                        0: 'любой'
                    };
                    for (let i in months) {
                        if (parseInt(i, 10) >= parseInt(month, 10)) {
                            this.months[index][i] = months[i];
                        }
                    }
                } else {
                    this.months[index] = months;
                }
            },
            continentDstChange() {
                this.form.cityDst = 'любой';
                this.form.cityDstHidden = 'любой';
                this.form.countryDst = 'любая';
                this.form.countryDstHidden = 'любая';
            },
            countrySrcBlur() {
                if (!this.form.countrySrcHidden) {
                    this.form.countrySrc = '';
                }
            },
            citySrcBlur() {
                if (!this.form.citySrcHidden) {
                    this.form.citySrc = '';
                }
            },
            countryDstBlur() {
                if (!this.form.countryDstHidden) {
                    this.form.countryDst = '';
                }
                this.form.cityDst = 'любой';
                this.form.cityDstHidden = 'любой';
            },
            cityDstBlur() {
                if (!this.form.cityDstHidden) {
                    this.form.cityDst = '';
                }
            },
            focus(conditionKey, valueKey, condition, value) {
                if (this.form[conditionKey] === condition) {
                    this.form[valueKey] = value;
                }
            },
            isNew() {
                return !this.formProp;
            },
            refreshForm() {
                if (this.isNew()) {
                    this.form = Object.assign({}, this.initForm);
                    this.datesCnt = 1;
                }
            },
            setAllDays() {
                for (let index in this.daysOfWeek) {
                    this.form.optional.days[index] = true;
                }
            },
            seasonSelect(season, index) {
                this.setMonth(this.seasonsMonths[season], index);
                this.form.date.month[index] = '0';
            },
            checkAllDays() {
                this.checkAllDaysVar = this.form.optional.days.every(elem => elem == true);
                this.allDaysDisable = false;
            },
            async generateJsonRequestDate(i) {
                let months = this.seasonMonthsInterval[this.form.date.season[i]];
                console.log('months ', months);
                let endDate = 30;
                if (months.to === '02') {
                    endDate = 28;
                }
                let dfgdfgdf = this.form.date.season[i] == 'winter' ? this.form.date.year[i] - 1 : this.form.date.year[i];
                let gte;
                let lt;
                if (this.form.date.month[i] == 0) {
                    gte = dfgdfgdf + '' + months.from + '01';
                    lt = this.form.date.year[i] + '' + months.to + endDate;
                } else {
                    let month = this.form.date.month[i];
                    if (month < 10) {
                        month = '0' + month;
                    }
                    gte = dfgdfgdf + '' + month + '' + '01';
                    lt = this.form.date.year[i] + '' + month + endDate;
                }

                if (this.form.date.year[i] === 'random') {
                    let arr = [];
                    let yearsData = [
                        '2019', '2020', '2021', '2022'
                    ];
                    let yearsDataNext = [
                        '2019', '2020', '2021', '2022'
                    ];
                    if (months.to == '12' || months.to == '01' || months.to == '02') {
                        yearsDataNext = [
                            '2020', '2021', '2022', '2023'
                        ];
                    }
                    for (let index in yearsData) {
                        arr.push(
                            {
                                "SrcDt": {
                                    $gte: parseInt(yearsData[index] + months.from + '01'),
                                    $lt: parseInt(yearsDataNext[index] + months.to + endDate)
                                }
                            }
                        );
                    }
                    return arr;
                }

                return [{"SrcDt": {$gte: parseInt(gte), $lt: parseInt(lt)}}];
            },
            async formToJsonRequest(outputArr) {
                translate.engine = 'yandex';
                translate.key = 'trnsl.1.1.20191125T185829Z.bafa3ff776a2185a.64a334987cf74f3b022d5c07a4b67f2247dbc05b';

                if (this.form.countrySrcHidden) {
                    this.filter['src.CountryCode'] = this.form.countrySrcHidden;
                }
                if (this.form.citySrcHidden && this.form.citySrcHidden !== 'любой') {
                    this.filter['src.name'] = await translate(this.form.citySrcHidden, {
                        from: 'ru', to: 'en'
                    });
                }
                if (this.form.continentDst != 'random') {
                    this.filter['Dst.continentCode'] = this.form.continentDst;
                }

                if (this.form.countryDstHidden && this.form.countryDstHidden !== 'любая') {
                    this.filter['Dst.CountryCode'] = this.form.countryDstHidden;
                }
                if (this.form.cityDstHidden && this.form.cityDstHidden !== 'любой') {
                    this.filter['dst.name'] = await translate(this.form.cityDstHidden, {
                        from: 'ru', to: 'en'
                    });
                }
                // в одну
                // "DstDt": null,
                // или обе стороны
                // "DstDt": {$ne: null},
                if (!this.form.forward && this.form.dual) {
                    this.filter['DstDt'] = {$ne: null};
                } else if (this.form.forward && !this.form.dual) {
                    this.filter['DstDt'] = null;
                }

                // по дате вылета (дата в формате yyyyMMdd, $gte - больше или равно, $lt - меньше)
                this.filter['$or'] = [];
                let i = 1;
                while (i <= this.datesCnt) {
                    let res = await this.generateJsonRequestDate(i);
                    this.filter['$or'] = this.filter['$or'].concat(res);
                    i++;
                }

                // цена (к примеру, меньшу 20000)
                //"Price" :
                let randomCost = document.querySelector('.randomCost .el-checkbox__original');
                if (!randomCost.checked) {
                    this.filter['Price'] = {$gte: this.form.costMin, $lte: this.form.costMax};
                }

                if (this.optional) {
                    // выгодность (к примеру, больше 30 процентов)
                    //"Discount": {$gte: 30},
                    this.filter['Discount'] = {$gte: parseInt(this.form.optional.profitability)};
                    // день недели вылета (0 - вс, 1 - пн, ...), $in - совпадение по одному из элементов массива (к примеру, выходные)
                    //"SrcDayOfWeek": {$in: [6, 0]}
                    let daysArr = [];
                    for (let index in this.form.optional.days) {
                        if (this.form.optional.days[index] == true) {
                            let number = parseInt(index);
                            daysArr.push(number);
                        }
                    }

                    this.filter['SrcDayOfWeek'] = {$in: daysArr};
                    // погода в день приземления днем С
                    //"Temp.Max": {$gte: 10},
                    // open
                    if (this.showAddionalWeather) {
                        this.filter['Temp.Max'] = {
                            $gte: parseInt(this.form.optional.dayTemp.min),
                            $lt: parseInt(this.form.optional.dayTemp.max)
                        };
                        this.filter['Temp.Min'] = {
                            $gte: parseInt(this.form.optional.nightTemp.min),
                            $lt: parseInt(this.form.optional.nightTemp.max)
                        };
                    }
                }
                console.log('this.filter ', this.filter);
                try {
                    var resp = await fetch('/save-user-api-request', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8'
                        },
                        body: JSON.stringify({
                            id: this.id,
                            content: this.filter,
                            user_id: window.constants.USER_ID,
                            interval: this.form.interval,
                            updated: this.form.updated,
                            limit: this.form.limit,
                            output: outputArr
                        })
                    });
                    var json = await resp.json();
                    this.refreshForm();
                } catch (e) {
                    console.log('err ', e);
                }
                console.log('Успех:', json);
            },
            async sendRequestAcception(outputArr) {
                try {
                    await this.formToJsonRequest(outputArr);
                    this.displayModal = false;
                    window.scrollTo(0, 0);
                } catch {
                    console.log('couldn\'t save a request');
                }
            },
            allDaysClick() {
                this.daysShow = !this.daysShow;
                this.allDaysDisable = true;
                this.checkAllDaysVar = true;
            },
            addDate() {
                if (this.datesCnt < 3) {
                    this.datesCnt++;
                    setTimeout(() => {
                        let yearSrc = $('.yearSrc' + this.datesCnt);
                        let scrollDiff = yearSrc.offset().top - $('html').scrollTop();
                        $('html').animate({
                            scrollTop: yearSrc.offset().top - 110
                        }, 10);
                    }, 300);
                }
            },
            subDate() {
                if (this.datesCnt > 1) {
                    this.datesCnt--;
                    setTimeout(() => {
                        let yearSrc = $('.yearSrc' + this.datesCnt);
                        let scrollDiff = yearSrc.offset().top - $('html').scrollTop();
                        $('html').animate({
                            scrollTop: yearSrc.offset().top - 110
                        }, 10);
                    }, 300);
                }
            },
            randomCostClick() {
                let randomCostLabel = document.querySelector('.randomCost .el-checkbox__original');
                if (randomCostLabel.checked) {
                    let sliderCost = document.getElementById('sliderCost');
                    sliderCost.noUiSlider.set([window.constants.COST_MIN, window.constants.COST_MAX]);
                }
            },
            clickDayTempRandom() {
                if (this.sliderDayTempLabelChecked) {
                    let sliderDayTemp = document.getElementById('sliderDayTemp');
                    sliderDayTemp.noUiSlider.set([-50, 50]);
                }
            },
            clickNightTempRandom() {
                if (this.sliderNightTempLabelChecked) {
                    let sliderNightTemp = document.getElementById('sliderNightTemp');
                    sliderNightTemp.noUiSlider.set([-50, 50]);
                }
            },
            intervalChange() {
                this.updatedArrBuffer = this.updatedArr.filter(
                    (obj) => {
                        return parseInt(obj.value, 10) <= parseInt(this.form.interval, 10)
                    }
                );

                this.form.updated = parseInt(this.form.interval, 10)
            },
            setCurrencyForUrl() {
                this.form.currencyForUrl = this.form.currency;
            },
            async makeCountryList(
                url,
                selector = '.countryDst .el-input__inner',
                country = 'countryDst',
                countryHidden = 'countryDstHidden',
                callback = undefined,
                citySelector = '.cityDst .el-input__inner',
                city = 'cityDst',
                cityHidden = 'cityDstHidden',
                dstFlag = true
            ) {
                $(selector).autocomplete({
                    autoFocus: true,
                    source: async (request, response) => {
                        this.form[countryHidden] = '';
                        const countries = await fetch(url, {
                            method: 'GET'
                        });
                        let countriesArr = await countries.json();
                        let resp = $.map(countriesArr, (item) => {
                            if (item.nameTranslations.ru.toLowerCase().indexOf(request.term.toLowerCase()) == 0) {
                                return {
                                    value: item.code,
                                    label: item.nameTranslations.ru,
                                }
                            }
                        });
                        if (dstFlag) {
                            resp.push({value: 'любая', label: 'любая'});
                        }
                        response(resp);
                    },
                    focus: (event, ui) => {
                        event.preventDefault();
                    },
                    select: (event, ui) => {
                        event.preventDefault();
                        this.setCountrySrcAndMakeCitySrcAutocomplete(
                            ui.item.label, ui.item.value, country,
                            countryHidden, citySelector, city, cityHidden, callback,
                            dstFlag
                        );
                    },
                    minLength: 2
                });
            },
            async getCities(selector = '.cityDst .el-input__inner',
                            city = 'cityDst',
                            cityHidden = 'cityDstHidden',
                            countryHidden = 'countryDstHidden',
                            dstFlag = true
            ) {
                setTimeout(() => {
                    $(selector).autocomplete({
                        source: async (request, response) => {
                            this.form[cityHidden] = '';
                            let url = 'https://api.cheapflights.sale/api/Cities/byCountry/' + this.form[countryHidden];
                            const cities = await fetch(url, {
                                method: 'GET'
                            });
                            let citiesArr = await cities.json();
                            let resp = $.map(citiesArr, (item) => {
                                if (item.nameTranslations && item.nameTranslations.ru &&
                                    item.nameTranslations.ru.toLowerCase().indexOf(request.term.toLowerCase()) == 0) {
                                    return {
                                        value: item.nameTranslations.ru,
                                    }
                                }
                            });
                            if (dstFlag) {
                                resp.push({value: 'любой'});
                            }
                            response(resp);
                        },
                        select: (event, ui) => {
                            event.preventDefault();
                            console.log('ui.item ', ui.item);
                            this.form[city] = ui.item.value;
                            this.form[cityHidden] = ui.item.value;
                        },
                        minLength: 2
                    });
                }, 1000);
            },
            modalShow() {
                this.$refs['form'].validate((valid) => {
                    if (valid) {
                        this.displayModal = true;
                    } else {
                        scroll(0, 0);
                        return false;
                    }
                });
            },
            detectCurrentDate() {
                let dt = new Date();
                let season = this.detectSeason(dt.getMonth());
                Vue.set(this.form.date, 'year', {
                    1: dt.getFullYear().toString(10),
                    2: dt.getFullYear().toString(10),
                    3: dt.getFullYear().toString(10)
                });
                this.selectYear(1);
                this.selectYear(2);
                this.selectYear(3);
                Vue.set(this.form.date.season, 1, season);
                Vue.set(this.form.date.season, 2, season);
                Vue.set(this.form.date.season, 3, season);
                Vue.set(this.form.date, 'month', {1: '0', 2: '0', 3: '0'});
            },
            async fillFormFields() {
                this.datesCnt = this.formProp.datesCnt;
                this.id = this.formProp.id;
                let month = Object.assign({}, this.formProp.date.month);
                this.form = Object.assign(this.form, this.formProp);
                setTimeout(() => {
                    this.form.date.month = month;
                }, 500);
                if (this.formProp.optional) {
                    this.optional = true;
                    this.allDaysClick();
                    this.checkAllDays();
                    if (this.formProp.optional.dayTemp) {
                        this.showAddionalWeather = true;
                    }
                }
                this.form.interval = this.formProp.interval.toString(10);
                this.wasCreated = true;
                let countrySrcHidden = await getCountryCode(this.form.countrySrc);
                this.setCountrySrcAndMakeCitySrcAutocomplete(
                    this.form.countrySrc,
                    countrySrcHidden,
                    'countrySrc',
                    'countrySrcHidden',
                    '.citySrc .el-input__inner',
                    'citySrc',
                    'citySrcHidden',
                    this.getCities,
                    false
                );
            }
        }
    }
</script>
