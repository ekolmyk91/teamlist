import React, {Component} from 'react'
import MemberPreview from './MemberPreview'
import MemberInfoPopup from './MemberInfoPopup'
import Sidebar from './Sidebar'
import {getUsers} from '../api/Api'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';

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
        $('body').toggleClass("m-ovrf")
    }

    renderMember = member => {
        return (
            <div className='team-box__card' data-trn={member.trainee} key={member.user_id}>
                <MemberPreview member={member} showPopup={this.togglePopup.bind(this, member.user_id)}/>
                {this.state.showPopupId ==  member.user_id ?
                    <MemberInfoPopup member={member} stateClass={this.state.stateClass} closePopup={this.togglePopup.bind(this)} /> : null
                }
            </div>
        );
    };

    updateDepartment = (departament, name) => {
        this.setState({ departament: name });
    }

    updatePosition = (position, name) => {
        this.setState({ position: name });
    }
  
    onchange = e => {
        let formatedValue = e.target.value.toLowerCase()
        this.setState({ search: formatedValue });
    };

    render() {

        const { t } = this.props;

        const { search } = this.state;

        const { members } = this.state;

        const filteredMembers = Object.values(members).filter(member => {
       
            if (search != '') {
                if (member.name.toLowerCase().indexOf(search) !== -1) {
                    return member.name.toLowerCase().indexOf(search) !== -1;
                } 
                if (member.surname.toLowerCase().indexOf(search) !== -1) {
                    return member.surname.toLowerCase().indexOf(search) !== -1;
                }
            } else if (this.state.departament != undefined && this.state.position == undefined) {
                    if (member.department.name == this.state.departament) {
                        return member;
                    }
            } else if (this.state.position != undefined && this.state.departament == undefined) {
                    if (member.position.name == this.state.position) {
                    return member;
                    }
            } else if (this.state.position != undefined && this.state.departament != undefined)  {
                    if (member.department.name == this.state.departament && member.position.name == this.state.position) {
                        return member;
                    }
            } else {
                    return member;
            }

        });

        return (
            <div>
                <section className="pageHeaderForm">
                    <div className="wrapper">
                        <h2>{t(data.team)}</h2>
                    </div>
                </section>
                <div className="container">
                    <div className="wrapper searchWrap">
                        <input className="js-widthInput" type="text" ref={input => this.search = input} onChange={this.onchange} placeholder={data.search} name="s" />
                    </div>
                    <section className="team-page">
                        <div className="wrapper blockFlex">
                            <div className="mainContent">
                                <div className="team-box">
                                    {filteredMembers.map(member => {
                                        return this.renderMember(member);
                                    })}
                                </div>
                            </div>
                            <div className="sidebar">
                                <Sidebar updateDepartment={this.updateDepartment} updatePosition={this.updatePosition}/>   
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        );
    }
}

export default withTranslation()(TeamList)

  