import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InstitutionalWithdrawals } from './institutional-withdrawals';

describe('InstitutionalWithdrawals', () => {
  let component: InstitutionalWithdrawals;
  let fixture: ComponentFixture<InstitutionalWithdrawals>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InstitutionalWithdrawals]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InstitutionalWithdrawals);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
