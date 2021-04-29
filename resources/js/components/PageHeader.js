import React, {Component} from 'react'
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';

class MemberSearch extends Component {

    render () {
        const { t } = this.props;
        return (
            <section className="pageHeaderForm">
                <div className="wrapper">
                    <h2>{t(data.team)}</h2>
                </div>
            </section>
        )
    }
}

export default withTranslation()(MemberSearch)