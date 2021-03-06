import React, {Component} from 'react'

class MemberInfoPopup extends Component {
    render () {
        const member = this.props.member;

        member.about = (member.about) ? member.about : '-';
        member.start_work_day = (member.start_work_day) ? new Date(member.start_work_day).toLocaleDateString('en-GB') : '-';

        return (
            <div className={'team-box__popup blockFlex ' + this.props.stateClass}>
                <div className="close-icon">
                    <a href='#' onClick={this.props.closePopup}>close</a>
                {/*<svg><use xlink:href="#close-icon"></use></svg>*/}
                </div>
                <div className="img">
                    <img src={member.user.avatar} alt="develop image" />
                </div>
                <div className="info blockFlex">
                    <div className="dev-name">
                        {member.name} {member.surname}
                    </div>
                    <div className="date-birth info__text">
                        <span className="info__label">Date-birth: </span>
                        {(new Date(member.birthday).toLocaleDateString('en-GB', {
                                month: '2-digit',day: '2-digit'}))}
                    </div>
                    <div className="date-start info__text">
                        <span className="info__label">First work day: </span>
                        {member.start_work_day}
                    </div>
                    <div className="department info__text">
                        <span className="info__label">Department: </span>
                        {member.department.name}
                    </div>
                    <div className="position info__text">
                        <span className="info__label">Position: </span>
                        {member.position.name}
                    </div>
                    <div className="about info__text">
                        <span className="info__label">About: </span>
                        <div dangerouslySetInnerHTML={{ __html: member.about }} />
                    </div>
                    {/*<div className="skills info__text">
                        <span className="info__label">Skills: </span>
                        Html, Css, Js, jQuery, Vue Js
                    </div>*/}
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

export default MemberInfoPopup
