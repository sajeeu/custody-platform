import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MyDeposits } from './my-deposits';

describe('MyDeposits', () => {
  let component: MyDeposits;
  let fixture: ComponentFixture<MyDeposits>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MyDeposits]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MyDeposits);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
