import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';

class VacationStatistic extends Component {
    renderList = obj => {
        return(
            <ul className='statistic__list'>
                {Object.keys(obj).map(key => (
                    <li key={key} className="statistic__item">
                        <span className='statistic__item__key'>{key} : </span>
                        <span className='statistic__item__value'>{obj[key]}</span>
                    </li>
                ))}
            </ul>
        )
    }
    render () {
        const { t } = this.props;
        const statistic = this.props.statistic;
        if(!!statistic) {
            return (
                <div className='vacation__statistic'>
                    <div className='statistic__block m-rest'>
                        <p className='statistic__title'>{t(data.vacation_page.remnant)}</p>
                        {this.renderList(statistic.rest)}
                    </div>
                    <div className='statistic__block m-used'>
                        <p className='statistic__title'>{t(data.vacation_page.used)}</p>
                        {this.renderList(statistic.used)}
                    </div>
                </div>
            )
        }else {
            return (
                <></>
            )
        }
    }
}

export default withTranslation()(VacationStatistic)
