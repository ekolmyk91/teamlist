import React, {Component} from 'react'

class MemberSearch extends Component {
    render () {
        return (
            <section className="pageHeaderForm">
                <div className="wrapper">
                    <h2>Команда</h2>
                    <form className="" method="get" action="search.html">
                        <input className="js-widthInput" type="text" value="" placeholder="Поиск сотрудников" name="s" />
                        <button className="" type="submit">
                            Поиск
                        </button>
                        <div className="hiddenSearch js-widthBlock">
                            <ul>
                                <li><a href="#">vfv</a></li>
                            </ul>
                        </div>
                    </form>

                </div>
            </section>
        )
    }
}

export default MemberSearch