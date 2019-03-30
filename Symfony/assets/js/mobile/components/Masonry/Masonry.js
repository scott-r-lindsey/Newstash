import React from 'react';
import MasonryLayout from 'react-masonry-layout';
import PropTypes from 'prop-types';
import MasonryItem from "./MasonryItem";
import PinnedPost from "./PinnedPost";

const gutter = 20;

class Masonry extends React.Component {

  constructor(props) {
    super(props);

    const server = typeof window == 'undefined';

    this.state = {
      perPage: 10,
      items: Array(20).fill(),
      itemWidth: this.findTargetWidth(server),
      server: server,
      items: props.initialItems,
    }
  }

  fetchAdditionalItems = () => {
    this.props.fetchAdditionalItems(
      items => {
        //console.log(items);
        this.setState({
          'items': [...this.state.items, ...items]
        });
      }
    );
  }

  findTargetWidth(server) {
    if (!server) {
      return (window.innerWidth / 2) - (( gutter * 3 )/2);
    }
    return 0;
  }

  updateDimensions() {

    if (!this.state.server) {
      this.setState( 
        { itemWidth: (window.innerWidth / 2) - (( gutter * 3 )/2) },
        () => console.log(this.layoutRef.current.getBricksInstance().pack())
      );
    }
    else{
      // server side?
    }
  }

  componentDidMount() {
    this.updateDimensions();
    if (!this.state.server) {
      window.addEventListener("resize", this.updateDimensions.bind(this));
    }
    console.log('sup');
  }

  componentWillUnmount() {
    if (!this.state.server) {
      window.removeEventListener("resize", this.updateDimensions.bind(this));
    }
  }

  render() {

    const { fetchAdditionalItems } = this.props;
    this.layoutRef = React.createRef();

    let width = this.state.itemWidth;
    let pinned = [];
    let i = 0;

    while (this.state.items[i].pinned) {
      pinned.push(this.state.items[i]);
      i++;
    }

    return (
      <div className="App">

        {pinned.map(item => {
          return (
            <PinnedPost
              item={item}
              width={width + gutter + width}
              key={item.sig}
            />
          )
        })}

        <MasonryLayout
          id="masonry-layout"
          ref={this.layoutRef}
          infiniteScrollContainer="main-container"
          infiniteScroll={this.fetchAdditionalItems}
          infiniteScrollDistance={700}
          sizes={ [
            { columns: 2, gutter: 20 },
            { mq: '768px', columns: 3, gutter: 20 },
            //{ mq: '1024px', columns: 6, gutter: 20 }
          ] }
          style={{
            marginLeft:'auto',
            marginRight:'auto'
          }}
        >

          {this.state.items.filter((item, i) => {
            return (i >= pinned.length) ? true : false;
          }).map(item  => {
            return (
              <MasonryItem
                item={item}
                width={width}
                key={item.sig} />
            )
          }, this)}

        </MasonryLayout>
      </div>
    );
  }
}

Masonry.propTypes = {
  initialItems: PropTypes.array.isRequired,
  fetchAdditionalItems: PropTypes.func.isRequired,
};

export default Masonry;
