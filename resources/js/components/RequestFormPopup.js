import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';
import DateFnsUtils from '@date-io/date-fns';
import {DatePicker, MuiPickersUtilsProvider} from '@material-ui/pickers';
import FormControl from '@material-ui/core/FormControl';
import NativeSelect from '@material-ui/core/NativeSelect';
import InputLabel from '@material-ui/core/InputLabel';
import {postRequest, getOffTimeTipes} from '../api/Api'

import { styled } from '@material-ui/core/styles';
import Button from '@material-ui/core/Button';
import { min } from 'lodash';


const RequestButton = styled(Button)({
    background: 'transparent',
    color: '#0e698a',
    padding: '10px',
    border: '1px solid #0e698a',
    borderRadius: '0',
    marginBottom: '2rem',
    '&:hover': {
        background: '#0e698a',
        borderColor: '#0e698a',
        color: 'white'
    }
});

const ErrorShow = {
    display: 'block',
    color: 'red',
    textAlign: 'center'
}

const ErrorHide = {
    display: 'done'
}

const SuccessShow = {
    display: 'block',
    color: 'green',
    textAlign: 'center'
}



class RequestFormPopup extends Component {

    constructor (props) {
		super(props)
        this.state = {
			selectedDateStart: '',
			selectedDateFinish: '',
            positions: [],
            selectValue: '',
            status: ErrorHide,
            messages: [],
            minDate: new Date()
		}
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

    numberСheck(num) {
        if(String(num).length === 1) {
            let updateNum = '0' + String(num)
            return updateNum
        } else {
            return num
        }
    }

    updateDate(date) {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        let curr_year = date.getFullYear();

        return curr_year + "-" + this.numberСheck(curr_month) + "-" + this.numberСheck(curr_date)
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
        this.setState({status: ErrorHide});
        this.setState({messages: []});

        if(new Date(this.state.selectedDateStart) <= new Date(this.state.selectedDateFinish)) {
            let date = new Date();
            date.setDate(date.getDate() + 2)

            if(+this.state.selectValue === 1 && new Date(date) > new Date(this.state.selectedDateStart)) {
                this.setState({status: ErrorShow});
                this.setState({messages: ['when choosing a vacation, start at least 3 days in advance']});
            } else {
                let data = $('.request__form').serialize();
                postRequest(data)
                    .then(response => {
                        this.setState({status: SuccessShow});
                        this.setState({messages: [`${response.message}`]});
                    })
                    .catch(error => {
                        let errors = error.response.data.message
                        if (Object.keys(errors).length > 0) {
                            this.setState({status: ErrorShow});
                            let arr = []
                            Object.keys(errors).map(key => {
                                arr.push(errors[key][0]);
                            })
                            this.setState({messages: arr});
                        }
                    })
            }
        } else {
            this.setState({status: ErrorShow});
            this.setState({messages: ['end date must not be less than start date']});
        }
    }

    resetForm = (e) => {
        this.props.closePopup();
        this.setState({selectValue: this.state.positions[0].id});
        this.setState({selectedDateStart: this.updateDate(new Date())});
        this.setState({selectedDateFinish: this.updateDate(new Date())});
        this.setState({status: ErrorHide});
        this.setState({messages: []});
    }

    render () {
        const { t } = this.props;

        if(this.props.request) {
            return (
                <div className='request__popup'>
                    <div className="close-icon">
                        <span href='#' onClick={this.resetForm}>{t(data.close)}</span>
                    </div>
                    <form onSubmit={this.handleSubmit} className='request__form'>
                        <div className='request__form__dates'>
                            <input type="hidden" name="user_id" id="user_id" value={this.props.user.roles[0].pivot.user_id}></input>
                            <MuiPickersUtilsProvider utils={DateFnsUtils}>
                                <DatePicker
                                    value={ this.state.selectedDateStart}
                                    onChange={(date) => this.handleDateChangeStart(date)}
                                    label="start day"
                                    minDate={this.state.minDate} />
                                <input type="hidden" name="start_day" id="start_day" value={ this.state.selectedDateStart}></input>
                                <DatePicker
                                    value={ this.state.selectedDateFinish}
                                    onChange={(date) => this.handleDateChangeFinish(date)}
                                    label="end day"
                                    minDate={this.state.minDate}/>
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
                        <RequestButton type="submit" className='request__form__btn'>Submit</RequestButton>
                    </form>
                    {this.state.messages.map(err => (<p style={this.state.status} key={err}>{err}</p>))}
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
