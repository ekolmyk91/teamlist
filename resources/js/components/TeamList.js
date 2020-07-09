import React, {Component} from 'react'
import MemberPreview from './MemberPreview'
import MemberInfoPopup from './MemberInfoPopup'
import {getUsers} from '../api/Api'

class TeamList extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
            error: null,
            isLoaded: false,
            showPopupId: false,
        }
    }

    componentDidMount () {
        getUsers().then(data => {
            console.log(data)
            this.setState({
                members: data,
                isLoaded: true
            });
        })
    }

    togglePopup(id, e) {
        this.setState({
            showPopupId: id ? id : null,
            stateClass: 'overlay--show'
        });
        $(".overlay").toggleClass("overlay--show")
    }

    render () {
        const { isLoaded, members } = this.state;
        if(!isLoaded){
            return <div className="preloader">Loading...</div>;
        }else{

            return (
                <section className="team-page">
                    <div className="wrapper blockFlex">
                        <div className="mainContent">
                            <div className="team-box">
                                { members.map((member) => {
                                    return (
                                        <div className='team-box__card' key={member.user_id}>
                                            <MemberPreview member={member} showPopup={this.togglePopup.bind(this, member.user_id)}/>
                                            {this.state.showPopupId ==  member.user_id ?
                                                <MemberInfoPopup member={member} stateClass={this.state.stateClass} closePopup={this.togglePopup.bind(this)} /> :
                                                null
                                            }
                                        </div>
                                    )
                                })}
                            </div>
                        </div>
                    </div>
                </section>
            )
        }
    }
}

export default TeamList