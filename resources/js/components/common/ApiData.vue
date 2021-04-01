<template>
    <div>
        <p v-if="showTitle">Мы покажем дешевые авиабилеты</p>
        <br v-if="showTitle">
        <div>
            Из страны <span
            class="selected-api-param">{{outputArr['countrySrc']?outputArr['countrySrc']:'любая'}}</span> и города
            <span class="selected-api-param">{{outputArr['citySrc']?outputArr['citySrc']:'любой'}}</span>
        </div>
        <div v-if="outputArr['continentDst']">
            В часть света <span class="selected-api-param">{{continents[outputArr['continentDst']]}}</span>
            и страну <span class="selected-api-param">{{outputArr['countryDst']?outputArr['countryDst']:'любая'}}</span>
        </div>
        <div>
            В город <span class="selected-api-param">{{outputArr['cityDst']?outputArr['cityDst']:'любой'}}</span>
        </div>
        <div v-if="outputArr['forward'] || outputArr['dual']">
            Направление полетов - <span class="selected-api-param" v-if="outputArr['forward']">туда</span>
            <span class="selected-api-param" v-if="outputArr['dual']">туда-обратно</span>
        </div>
        <div v-for="(date, index) in outputArr['dates']">
            Год вылета ({{index+1}}) <span
            class="selected-api-param">{{date['year']==='random'?'любой':date['year']}}</span>
            <br/>
            Время года ({{index+1}}) <span class="selected-api-param">{{seasonsTranscription[date['season']]}}</span>
            <br/>
            Месяц ({{index+1}}) <span class="selected-api-param">{{monthTranscription[date['month']]}}</span>
        </div>
        <div v-if="outputArr['passengers']">
            Пассажиры <span class="selected-api-param">{{passengersArr[outputArr['passengers']]}}</span>
        </div>
        <div>
            Цена от <span class="selected-api-param">{{outputArr['costMin']}}</span> до <span
            class="selected-api-param">{{outputArr['costMax']}}</span>
            <span class="selected-api-param">
                {{shortCurrencyTranslateFunc(outputArr['currency']?outputArr['currency'].toLowerCase():'')}}
            </span>
        </div>
        <br/>
        <div>
            Период показа <span class="selected-api-param">{{outputArr['interval']}} минут</span>
        </div>
        <div>
            Глубина поиска <span class="selected-api-param">{{outputArr['updated']}} минут</span>
        </div>
        <div>
            Максимальное количество билетов за 24 часа <span class="selected-api-param">{{outputArr['limit']==0?'все':outputArr['limit']}}</span>
        </div>
        <div v-if="outputArr['language']">
            Язык (туда-обратно) <span class="selected-api-param">{{languages[outputArr['language']]}}</span>
        </div>
        <div v-if="outputArr['currency']">
            Валюта (цена) <span class="selected-api-param">{{currencies[outputArr['currency']]}}</span>
        </div>
        <div v-if="outputArr['currency']">
            Валюта (метапоисковика) <span class="selected-api-param">{{currencies[outputArr['currencyForUrl']]}}</span>
        </div>
        <div v-if="outputArr['optional']">
            <b>Дополнительные параметры</b>
            <div v-if="outputArr['profitability']">
                Выгодность покупки больше <span class="selected-api-param">{{outputArr['profitability']}} %</span>
            </div>
            <div>
                Дни недели вылета <span class="selected-api-param">{{listDays(outputArr['days'])}}</span>
            </div>
            <div v-if="outputArr['showAddionalWeather']">
                Прогноз погоды в день прилета
                <div>
                    Днем от + (или -) <span
                    class="selected-api-param">{{parseInt(outputArr['dayTemp.min'], 10)}} С</span> до + (или -)
                    <span class="selected-api-param">{{parseInt(outputArr['dayTemp.max'], 10)}} С</span>
                </div>
                <div>
                    Ночью от + (или -) <span
                    class="selected-api-param">{{parseInt(outputArr['nightTemp.min'], 10)}} С</span> до + (или -)
                    <span class="selected-api-param">{{parseInt(outputArr['nightTemp.max'], 10)}} С</span>
                </div>
            </div>
        </div>
        <br/>
    </div>
</template>
<script>
    import {mapActions, mapState} from "vuex";
    import {requestServerPost, requestVkApiGet} from "../../helpers/request";
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import CKEditor from '@ckeditor/ckeditor5-vue';
    import {store} from '../../store';
    import {shortCurrencyTranslate} from "../../helpers";

    Vue.use(CKEditor);

    export default {
        props: {
            showTitle: {
                type: Boolean,
                default: true
            },
            outputArr: {},
        },
        data() {
            return {
                seasonsTranscription: window.constants.SEASONS_TRANSCRIPTION,
                monthTranscription: window.constants.MONTHS_TRANSCRIPTION,
                continents: window.constants.CONTINENTS,
                daysOfWeek: window.constants.DAYS_OF_WEEK,
            }
        },
        created() {
        },
        mounted() {
            console.log('outputArr ', this.outputArr);
        },
        computed: {
            ...mapState({
                currencies: (state) => state.main.currencies,
                languages: (state) => state.main.languages,
                passengersArr: (state) => state.main.passengers,
            }),
        },
        methods: {
            shortCurrencyTranslateFunc(currency) {
                return shortCurrencyTranslate(currency);
            },
            listDays(days) {
                let check = 0;
                days.every((element, index, array) => {
                    check++;
                    return element == true;
                });
                if (check === 7) {
                    return 'все';
                }
                let res = '';
                for (let index = 1; index < days.length; index++) {
                    if (days[index]) {
                        res += this.daysOfWeek[index].toLowerCase() + ', ';
                    }
                }
                res += this.daysOfWeek[0].toLowerCase();
                return res;
            }
        },
    }
</script>
