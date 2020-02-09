import React, {Component} from 'react'
import axios from 'axios'
import MemberPreview from './MemberPreview'
import MemberInfoPopup from './MemberInfoPopup'

class TeamList extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
            error: null,
            isLoaded: false,
            showPopupId: false
        }
    }

    componentDidMount () {
        axios.get('/api/members')
            .then(response => {
                // const members= response.data;
                this.setState({
                    members: response.data,
                    isLoaded: true
                });
            })
    }

    togglePopup(id, e) {
        this.setState({
            showPopupId: id ? id : null
        });
    }

    render () {
        const { isLoaded, members } = this.state;
        if(!isLoaded){
            return <div class="preloader">Loading...</div>;
        }else{

            return (
                <section className="team-page">
                    <div className="wrapper blockFlex">
                        <div className="mainContent">
                            <div className="team-box">
                                { members.map((member) => {
                                    return (
                                        <div className='team-box__card' data-id='asd' key={member.user_id}>
                                            <MemberPreview member={member} showPopup={this.togglePopup.bind(this, member.user_id)}/>
                                            {this.state.showPopupId ==  member.user_id ?
                                                <MemberInfoPopup member={member} closePopup={this.togglePopup.bind(this, null)} /> :
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