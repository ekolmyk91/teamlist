import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';

class VacationInfoPopup extends Component {
    render () {
        const member = this.props.member;
        const { t } = this.props;
        return (
            <div className={'vacation-popup ' + this.props.stateClass}>
                <div className="close-icon">
                    <span href='#' onClick={this.props.closePopup}>{t(data.close)}</span>
                </div>
                <div className="vacation-popup__img">
                    <img src={member.user.avatar} alt="develop image" />
                </div>
                <div className="vacation-popup__info">
                    <div className="vacation-popup__name">
                        {member.name} {member.surname}
                    </div>
                </div>
            </div>
        )
    }
}

export default withTranslation()(VacationInfoPopup)
