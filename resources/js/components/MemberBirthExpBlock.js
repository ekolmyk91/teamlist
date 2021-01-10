import React, {Component} from 'react'
import moment from 'moment'
import {getBirthExpPeople} from '../api/Api'
import 'bootstrap/dist/css/bootstrap.css'

class MemberBirthExpBlock extends Component {

    constructor (props) {
        super(props)
        this.state = {
            monthValue: moment().month(),
            birthPeople: [],
            expPeople: []
        }
        this.handleMonthChange = this.handleMonthChange.bind(this);
    }

    componentDidMount () {
        getBirthExpPeople(this.state.monthValue).then(data => {
            this.setState({
                birthPeople: data.birthPeople,
                expPeople: data.expPeople,
            });
        })
    }

    handleMonthChange(event) {
        this.setState({monthValue: event.target.value});
        event.preventDefault();
    }

    componentDidUpdate(prevProps, prevState) {
        if (this.state.monthValue !== prevState.monthValue) {
            getBirthExpPeople(this.state.monthValue).then(data => {
                this.setState({
                    birthPeople: data.birthPeople,
                    expPeople: data.expPeople,
                });
            })
        }
    }

    renderMonthsList = () => {
        const monthList = moment.months();
        return (
            <div className="filter-inner">
                <select value={this.state.monthValue} onChange={this.handleMonthChange}>
                    {monthList.map((month, index) => (
                        <option key={index} value={index}>{month}</option>
                    ))}
                </select>
            </div>
        );
    }

    renderBirthPeople = () => {
        const renderBirthPeople = this.state.birthPeople.map( (member, id) => (
            <li className="filter-name" key={member.user_id}>
               {member.name} {member.surname} {(new Date(member.birthday).toLocaleDateString('en-GB', {
                    month: '2-digit',day: '2-digit'}))}
            </li>
        ));
        return (
            <div>
                <ul>
                    {renderBirthPeople}
                </ul>
            </div>
        );
    }

    renderExpPeople = () => {
        const renderExpPeople = this.state.expPeople.map( (member, id) => (
            <li className="filter-name" key={member.user_id}>
                {member.name} {member.surname} {(new Date().getFullYear() - new Date(member.start_work_day).getFullYear())} yr
            </li>
        ));
        return (
            <div>
                <ul>
                    {renderExpPeople}
                </ul>
            </div>
        );
    }

    render() {
        return (
                <div className="card">
                    <div className="card-body">
                        {this.renderMonthsList()}
                        <p><b>Birthdays</b></p>
                        {this.renderBirthPeople()}
                        <p><b>How long have you been with WEB4PRO?</b></p>
                        {this.renderExpPeople()}
                    </div>
                </div>
        );
    }
}

export default MemberBirthExpBlock
