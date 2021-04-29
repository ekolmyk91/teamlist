import React, {Component} from 'react'
import {withTranslation} from 'react-i18next'
import data from '../data/data.json';

class HomePage extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
        }
    }

    state = {
        search: ""
    };


    render() {
        const { t } = this.props;
        return (
            <div className="container">
                <section className="home-page">
                    <div className="wrapper">
                        <span>{t(data.homepage)}</span>
                    </div>
                </section>
            </div>
        );
    }
}

export default withTranslation()(HomePage)

  