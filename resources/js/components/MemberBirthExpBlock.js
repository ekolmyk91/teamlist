import React, {PureComponent} from 'react'
import moment from 'moment'
import {getBirthExpPeople} from '../api/Api'
import 'bootstrap/dist/css/bootstrap.css'

class MemberBirthExpBlock extends PureComponent {

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
            <tr key={member.user_id}>
                <td>{member.name}</td>
                <td>{member.surname}</td>
                <td>
                    {(new Date(member.birthday).toLocaleDateString('en-GB', {
                    month: '2-digit',day: '2-digit'}))}
                </td>
            </tr>
        ));
        return (
            <div>
                <table>
                    <tbody>
                    {renderBirthPeople}
                    </tbody>
                </table>
            </div>
        );
    }

    renderExpPeople = () => {
        const renderExpPeople = this.state.expPeople.map( (member, id) => (
            <tr key={member.user_id}>
                <td>{member.name}</td>
                <td>{member.surname}</td>
                <td>
                    {(new Date().getFullYear() - new Date(member.start_work_day).getFullYear())} yr
                </td>
            </tr>
        ));
        return (
            <div>
                <table>
                    <tbody>
                    {renderExpPeople}
                    </tbody>
                </table>
            </div>
        );
    }

    render() {
        return (
            <div>
                <div>
                    {this.renderMonthsList()}
                </div>
                <div className="card">
                    <div className="card-header">
                        <span className="filter-tittle">Birthdays</span>
                    </div>
                    <div className="card-body">
                        {this.renderBirthPeople()}
                    </div>
                </div>
                <br/>
                <div className="card">
                    <div className="card-header">
                        <span className="filter-tittle">How long have you been with WEB4PRO?</span>
                    </div>
                    <div className="card-body">
                        {this.renderExpPeople()}
                    </div>
                </div>
            </div>
        );
    }
}

export default MemberBirthExpBlock
