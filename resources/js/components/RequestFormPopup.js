import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';
import DateFnsUtils from '@date-io/date-fns';
import {DatePicker, MuiPickersUtilsProvider} from '@material-ui/pickers';
import FormControl from '@material-ui/core/FormControl';
import NativeSelect from '@material-ui/core/NativeSelect';
import InputLabel from '@material-ui/core/InputLabel';
import {postRequest, getOffTimeTipes} from '../api/Api'



class RequestFormPopup extends Component {

    constructor (props) {
		super(props)
        this.state = {
			selectedDateStart: '',
			selectedDateFinish: '',
            positions: [],
            selectValue: '',
		}
	}
    updateDate(date) {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        let curr_year = date.getFullYear();
        return curr_year + "-" + curr_month + "-" + curr_date
    }

    componentDidMount () {
        getOffTimeTipes().then(response => {
			this.setState({
				positions: response,
			});
            this.setState({selectValue: this.state.positions[0].id});
            this.setState({selectedDateStart: this.updateDate(new Date())});
            this.setState({selectedDateFinish: this.updateDate(new Date())});
		})
    }

    handleDateChangeStart = (date) => {
        this.setState({selectedDateStart: this.updateDate(date)});
    }
    handleDateChangeFinish = (date) => {
        this.setState({selectedDateFinish: this.updateDate(date)});
    }
    handleChangeSelect = (e) => {
        this.setState({selectValue: e.target.value});
    }
    handleSubmit = (e) => {
        e.preventDefault();
        let data = $('.request__form').serialize()
        console.log(data)
        postRequest(data).then(response => {
			console.log(response)
		})
    }
    render () {
        const { t } = this.props;

        if(this.props.request) {
            return (
                <div className='request__popup'>
                    <div className="close-icon">
                        <span href='#' onClick={this.props.closePopup}>{t(data.close)}</span>
                    </div>
                    <form onSubmit={this.handleSubmit} className='request__form'>
                        <div className='request__form__dates'>
                            <input type="hidden" name="user_id" id="user_id" value={this.props.user.roles[0].pivot.user_id}></input>
                            <MuiPickersUtilsProvider utils={DateFnsUtils}>
                                <DatePicker value={ this.state.selectedDateStart} onChange={(date) => this.handleDateChangeStart(date)} label="start day"/>
                                <input type="hidden" name="start_day" id="start_day" value={ this.state.selectedDateStart}></input>
                                <DatePicker value={ this.state.selectedDateFinish} onChange={(date) => this.handleDateChangeFinish(date)} label="end day"/>
                                <input type="hidden" name="end_day" id="end_day" value={ this.state.selectedDateFinish}></input>
                            </MuiPickersUtilsProvider>
                        </div>
                        <FormControl>
                            <InputLabel shrink htmlFor="age-native-label-placeholder">position</InputLabel>
                            <NativeSelect
                                value={this.state.selectValue}
                                onChange={this.handleChangeSelect}
                                inputProps={{
                                    name: 'type_id',
                                    id: 'type_id',
                                }}
                            >
                                {this.state.positions.map(position => {
                                    return (
                                        <option value={position.id} key={position.id}>{position.name}</option>
                                    )
                                })}
                            </NativeSelect>
                        </FormControl>
                        <input type="submit" value="Submit" className='request__form__btn'/>
                    </form>
                </div>
            )
        } else {
            return (
                <></>
            )
        }
    }
}

export default withTranslation()(RequestFormPopup)
