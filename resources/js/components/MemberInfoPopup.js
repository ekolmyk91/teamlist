import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';

class MemberInfoPopup extends Component {
    render () {
        const member = this.props.member;
        const { t } = this.props;
        member.about = (member.about) ? member.about : '-';
        return (
            <div className={'team-box__popup blockFlex ' + this.props.stateClass}>
                <div className="close-icon">
                    <span href='#' onClick={this.props.closePopup}>{t(data.close)}</span>
                </div>
                <div className="img">
                    <img src={member.user.avatar} alt="develop image" />
                </div>
                <div className="info blockFlex">
                    <div className="dev-name">
                        {member.name} {member.surname}
                    </div>
                    { member.phone_1  ?
                        <div className="dev-phone">
                            <span className="info__label">{t(data.member.phone)}</span>
                            {member.phone_1}
                        </div>
                        :
                        ''
                    }
                    <div className="date-birth info__text">
                        <span className="info__label">{data.member.date}</span>
                        {member.birthday}
                    </div>
                    <div className="date-start info__text">
                        <span className="info__label">{t(data.member.first_day)}</span>
                        {member.start_work_day}
                    </div>
                    <div className="department info__text">
                        <span className="info__label">{t(data.member.department)}</span>
                        {member.department.name}
                    </div>
                    <div className="position info__text">
                        <span className="info__label">{t(data.member.position)}</span>
                        {member.position.name}
                    </div>
                    <div className="about info__text">
                        <span className="info__label">{t(data.member.about)}</span>
                        <div dangerouslySetInnerHTML={{ __html: member.about }} />
                    </div>
                    <ul>
                        <ul className="certificate">
                            {member.certificates.map(certificate => (
                                <li className="certificate__item" key={certificate.id}>
                                    <img src={certificate.logo} alt={certificate.name} />
                                </li>
                            ))}
                        </ul>
                    </ul>
                </div>
            </div>
        )
    }
}

export default withTranslation()(MemberInfoPopup)
