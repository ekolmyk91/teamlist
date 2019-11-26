import React, {Component} from 'react'

class MemberPreview extends Component {
    render () {
        const member = this.props.member
        return (
            <div className="team-box__item blockFlex" onClick={this.props.showPopup}>
                <div className="img">
                    <img src="/images/front/logo8.png" alt="develop image" />
                </div>
                <div className="info">
                    <div className="dev-name">{member.name}</div>
                    <div className="department info__text">{member.surname}</div>
                </div>
            </div>
        )
    }
}

export default MemberPreview