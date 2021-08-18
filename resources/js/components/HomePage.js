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
            <div>
                <section className="pageHeaderForm">
                    <div className="wrapper">
                        <h2>{t(data.home)}</h2>
                    </div>
                </section>
                <div className="container"> 
                    <section className="home-page">
                        <div className="home-team"><a href="/team"><span>{t(data.homepage_content.team)}</span> <i className="i team"></i></a></div>
                        <div className="home-team__inner sc">
                            <div className="home-team__card"><a href="https://forms.gle/PoY7USJyU2CU5ZLKA"><span>{t(data.homepage_content.request)}</span> <i className="i pc"></i></a></div>
                            <div className="home-team__card"><a href="https://forms.gle/iqcPKozbYZvhW5c36"><span>{t(data.homepage_content.ideas)}</span> <i className="i improvments"></i></a></div>
                        </div>
                        <div className="home-team__inner thr">
                            <div className="home-team__card"><a href="https://docs.google.com/spreadsheets/d/1tlcx2XeaHB8YUAkhCnj-HRaKqZfqpf6Zx4UQeix4A0w/edit?usp=sharing"><span>{t(data.homepage_content.vacation)}</span> <i className="i chill"></i></a></div>
                            <div className="home-team__card"><a href="https://docs.google.com/spreadsheets/d/1So5tPF_XtdbkwsDDFRievQNmk78QMw0PJH6JdywXH3s/edit?usp=sharing"><span>{t(data.homepage_content.library)}</span> <i className="i library"></i></a></div>
                            <div className="home-team__card"><a href="https://drive.google.com/drive/folders/0B2IE7CeXw-wTcWJnQ2c0NVg0aEk?resourcekey=0-7neGEfYTLDkosvctEKYUDg&usp=sharing"><span>{t(data.homepage_content.photo)}</span> <i className="i photo"></i></a></div>
                        </div>
                        <div className="home-team__inner sc">
                            <div className="home-team__card"><a href="http://corp.web4pro.net/"><span>{t(data.homepage_content.corp)}</span> <i className="i blog"></i></a></div>
                            <div className="home-team__card"><a href="https://pm.web4pro.com.ua/"><span>{t(data.homepage_content.redmine)}</span> <i className="i redmine"></i></a></div>
                        </div>
                    </section>
                </div>
            </div>
        );
    }
}

export default withTranslation()(HomePage)

  