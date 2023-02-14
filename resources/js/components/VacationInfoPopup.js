import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';
import {getUsersId} from '../api/Api';
import VacationStatistic from './VacationStatistic';

class VacationInfoPopup extends Component {
    constructor (props) {
        super(props)
        this.state = {
            info: [],
            vacationBlock: null
        }
    }
    componentDidMount () {
        getUsersId(this.props.userId).then(data => {
            this.setState({
                info: data
            });
        });
    }

    numberСheck(num) {
        if(String(num).length === 1) {
            let updateNum = '0' + String(num)
            return updateNum
        } else {
            return num
        }
    }

    updateDate(date) {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        let curr_year = date.getFullYear();

        return curr_year + "/" + this.numberСheck(curr_month) + "/" + this.numberСheck(curr_date)
    }
    renderVacation(vacation) {
        if(vacation) {
            let startDate = this.updateDate(vacation.start)
            let endDate = this.updateDate(vacation.end)
            console.log(startDate, endDate)
            return (
                <div className="vacation-popup__vacation">
                    <span className="vacation-popup__label">{vacation.type}:</span>
                    <span>{startDate} - {endDate}</span>
                </div>
            )
        } else {
            return (
                <></>
            )
        }
    }
    render () {
        const member = this.props.member;
        const { t } = this.props;

        if(this.props.vacation) {
            console.log(this.props.vacation)
            this.state.vacationBlock = this.renderVacation(this.props.vacation)
        }

        if(this.state.info.name) {
            return (
                <div className={'vacation-popup ' + this.props.stateClass}>
                    <div className="close-icon">
                        <span href='#' onClick={this.props.closePopup}>{t(data.close)}</span>
                    </div>
                    <div className="vacation-popup__img">
                        <img src={this.state.info.user.avatar} alt="develop image" />
                    </div>
                    <div className="vacation-popup__info">
                        <div className="vacation-popup__name">
                            {this.state.info.name} {this.state.info.surname}
                        </div>
                        {this.state.vacationBlock}
                        <div className="vacation-popup__text">
                            <span className="vacation-popup__label">{t(data.member.department)}</span>
                            {this.state.info.department ? this.state.info.department.name : '-'}
                        </div>
                        <div className="vacation-popup__text">
                            <span className="vacation-popup__label">{t(data.member.position)}</span>
                            {this.state.info.position ? this.state.info.position.name : '-'}
                        </div>
                        <VacationStatistic statistic={this.state.info.statistic}/>
                    </div>
                </div>
            )
        } else {
            return (
                <></>
            )
        }
    }
}

export default withTranslation()(VacationInfoPopup)
