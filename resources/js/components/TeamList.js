import React, {Component} from 'react'
import MemberPreview from './MemberPreview'
import MemberInfoPopup from './MemberInfoPopup'
import Sidebar from './Sidebar'
import {getUsers} from '../api/Api'

class TeamList extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
            error: null,
            isLoaded: false,
            showPopupId: false,
            search: "",
        }
        this.onchange = this.onchange.bind(this);
    }

    state = {
        search: ""
    };
    
    componentDidMount () {
        getUsers().then(data => {
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

    renderMember = member => {
        
        return (
            <div className='team-box__card' key={member.user_id}>
                <MemberPreview member={member} showPopup={this.togglePopup.bind(this, member.user_id)}/>
                {this.state.showPopupId ==  member.user_id ?
                    <MemberInfoPopup member={member} stateClass={this.state.stateClass} closePopup={this.togglePopup.bind(this)} /> : null
                }
            </div>
        );
    };

  
    onchange = e => {
        this.setState({ search: e.target.value });
    };

    render() {

        const { search } = this.state;

        const { members } = this.state;

        const filteredCountries = members.filter(member => {
          console.log(this.state);
          return member.name.toLowerCase().indexOf(search) !== -1;
        });

        return (
            <div className="container">
                <div className="wrapper searchWrap">
                    <input className="js-widthInput" type="text" ref={input => this.search = input} onChange={this.onchange} placeholder="Поиск сотрудников" name="s" />
                </div>
                <section className="team-page">
                    <div className="wrapper blockFlex">
                        <div className="mainContent">
                            <div className="team-box">
                                {filteredCountries.map(member => {
                                    return this.renderMember(member);
                                })}
                            </div>
                        </div>
                        <div className="sidebar">
                            <Sidebar />   
                        </div>
                    </div>
                </section>
            </div>
        );
    }
}

export default TeamList

  